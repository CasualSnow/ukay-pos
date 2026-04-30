<?php

class ReservationController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
    }

    public function index() {
        $db = getDB();
        $reservations = $db->query("
            SELECT r.*, i.name as item_name, i.price, i.image_url, i.tag_color
            FROM reservations r 
            JOIN items i ON r.item_id = i.id 
            ORDER BY r.created_at DESC
        ")->fetchAll();
        
        $this->view('pos/reservations', ['reservations' => $reservations]);
    }

    public function add() {
        $db = getDB();
        try {
            // Support both JSON and FormData/POST
            $json = json_decode(file_get_contents('php://input'), true);
            $itemId = $json['item_id'] ?? $_POST['item_id'] ?? null;
            $customerName = $json['customer_name'] ?? $_POST['customer_name'] ?? null;
            $contactNumber = $json['contact_number'] ?? $_POST['contact_number'] ?? null;
            $notes = $json['notes'] ?? $_POST['notes'] ?? null;

            // Validate inputs
            if (empty($customerName)) {
                throw new Exception("Customer name is required.");
            }
            if (!$itemId) {
                throw new Exception("Item ID is required.");
            }

            $db->beginTransaction();
            
            // Verify item exists and is still available
            $stmtCheck = $db->prepare("SELECT status FROM items WHERE id = ? FOR UPDATE");
            $stmtCheck->execute([$itemId]);
            $item = $stmtCheck->fetch();

            if (!$item) {
                throw new Exception("Item not found.");
            }
            if ($item['status'] !== 'available') {
                throw new Exception("Item is already " . $item['status'] . ".");
            }

            // Securely insert the reservation
            $stmt = $db->prepare("INSERT INTO reservations (item_id, customer_name, contact_number, notes, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$itemId, $customerName, $contactNumber, $notes]);
            
            // Update the item status
            $stmtUpdate = $db->prepare("UPDATE items SET status = 'reserved' WHERE id = ?");
            $stmtUpdate->execute([$itemId]);
            
            $db->commit();

            // Handle AJAX or Redirect
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false || $json !== null;

            if ($isAjax) {
                $this->json(['success' => true, 'message' => 'Item successfully reserved!']);
            } else {
                $this->redirect('/pos');
            }
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false || isset($json);
            
            if ($isAjax) {
                $this->json(['success' => false, 'message' => $e->getMessage()]);
            } else {
                die("Reservation Error: " . $e->getMessage());
            }
        }
    }

    public function delete() {
        $db = getDB();
        $id = $_POST['id'] ?? null;

        if (!$id) {
            $this->redirect('/reservations');
        }

        try {
            $db->beginTransaction();

            // Get item_id and status before deleting
            $stmt = $db->prepare("SELECT item_id, status FROM reservations WHERE id = ?");
            $stmt->execute([$id]);
            $res = $stmt->fetch();

            if ($res) {
                // If the reservation was not completed/cancelled, make the item available again
                if ($res['status'] === 'pending' || $res['status'] === 'paid') {
                    $stmtUpdate = $db->prepare("UPDATE items SET status = 'available' WHERE id = ?");
                    $stmtUpdate->execute([$res['item_id']]);
                }

                $stmtDel = $db->prepare("DELETE FROM reservations WHERE id = ?");
                $stmtDel->execute([$id]);
            }

            $db->commit();
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
        }

        $this->redirect('/reservations');
    }

    public function complete() {
        $db = getDB();
        $id = $_POST['id'];
        
        // When completing a reservation, if it was already paid, we just mark it as completed.
        // If it wasn't paid (though the UI should prevent this), we might need to handle it.
        $stmt = $db->prepare("SELECT r.*, i.status as item_status FROM reservations r JOIN items i ON r.item_id = i.id WHERE r.id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        
        $db->beginTransaction();
        $db->prepare("UPDATE reservations SET status = 'completed' WHERE id = ?")->execute([$id]);
        
        // Only set item back to available if it wasn't sold (paid)
        if ($res && $res['item_status'] !== 'sold') {
            $db->prepare("UPDATE items SET status = 'available' WHERE id = ?")->execute([$res['item_id']]);
        }
        
        $db->commit();
        
        $this->redirect('/reservations');
    }

    public function pay() {
        $db = getDB();
        $id = $_POST['reservation_id'];
        $payment_method = $_POST['payment_method'];
        $user_id = $_SESSION['user_id'];

        try {
            $db->beginTransaction();

            // Get reservation details
            $stmt = $db->prepare("
                SELECT r.*, i.price, i.tag_color, i.id as item_id 
                FROM reservations r 
                JOIN items i ON r.item_id = i.id 
                WHERE r.id = ? AND r.status = 'pending'
            ");
            $stmt->execute([$id]);
            $res = $stmt->fetch();

            if (!$res) {
                throw new Exception("Reservation not found or already processed.");
            }

            // Calculate discount based on tag color
            $tag_key = 'discount_' . $res['tag_color'];
            $stmtDisc = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
            $stmtDisc->execute([$tag_key]);
            $discount_rate = (float)$stmtDisc->fetchColumn();
            
            $discount_amount = $res['price'] * $discount_rate;
            $final_price = $res['price'] - $discount_amount;

            // 1. Create Sale record
            $stmtSale = $db->prepare("INSERT INTO sales (user_id, total_amount, payment_method) VALUES (?, ?, ?)");
            $stmtSale->execute([$user_id, $final_price, $payment_method]);
            $sale_id = $db->lastInsertId();

            // 2. Create Sale Item record
            $stmtSaleItem = $db->prepare("INSERT INTO sale_items (sale_id, item_id, price, discount, final_price) VALUES (?, ?, ?, ?, ?)");
            $stmtSaleItem->execute([$sale_id, $res['item_id'], $res['price'], $discount_amount, $final_price]);

            // 3. Update Item status to 'sold'
            $stmtItem = $db->prepare("UPDATE items SET status = 'sold' WHERE id = ?");
            $stmtItem->execute([$res['item_id']]);

            // 4. Update Reservation status to 'paid'
            $stmtRes = $db->prepare("UPDATE reservations SET status = 'paid' WHERE id = ?");
            $stmtRes->execute([$id]);

            $db->commit();
            $this->redirect('/reservations');
        } catch (Exception $e) {
            $db->rollBack();
            die($e->getMessage());
        }
    }

    public function cancel() {
        $db = getDB();
        $id = $_POST['id'];

        $stmt = $db->prepare("SELECT item_id FROM reservations WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();

        if ($res) {
            $db->beginTransaction();
            $db->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?")->execute([$id]);
            $db->prepare("UPDATE items SET status = 'available' WHERE id = ?")->execute([$res['item_id']]);
            $db->commit();
        }

        $this->redirect('/reservations');
    }
}
