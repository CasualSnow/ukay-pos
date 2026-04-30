<?php

class ReservationController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
        $db = getDB();
        $this->checkAndExpireReservations($db);
    }

    private function checkAndExpireReservations($db) {
        // Check if expiration_date column exists first
        $stmtCheckExpire = $db->query("SHOW COLUMNS FROM reservations LIKE 'expiration_date'");
        $hasExpiration = $stmtCheckExpire->fetch() !== false;
        
        if (!$hasExpiration) {
            return;
        }

        // Find all active reservations that are expired
        $stmt = $db->prepare("
            SELECT r.id, r.item_id 
            FROM reservations r 
            WHERE r.status IN ('reserved', 'pending') 
            AND r.expiration_date IS NOT NULL 
            AND r.expiration_date < NOW()
        ");
        $stmt->execute();
        $expiredReservations = $stmt->fetchAll();

        foreach ($expiredReservations as $res) {
            try {
                $db->beginTransaction();

                // Mark reservation as expired
                $stmtUpdateRes = $db->prepare('UPDATE reservations SET status = ? WHERE id = ?');
                $stmtUpdateRes->execute(['expired', $res['id']]);

                // Set item back to available
                $stmtUpdateItem = $db->prepare('UPDATE items SET status = ? WHERE id = ?');
                $stmtUpdateItem->execute(['available', $res['item_id']]);

                $db->commit();
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
            }
        }
    }

    public function index() {
        $db = getDB();

        // First, check and expire any expired reservations
        $this->checkAndExpireReservations($db);

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
            $customerName = trim($json['customer_name'] ?? $_POST['customer_name'] ?? '');
            $contactNumber = trim($json['contact_number'] ?? $_POST['contact_number'] ?? '');
            $notes = trim($json['notes'] ?? $_POST['notes'] ?? '');
            $durationDays = isset($json['duration_days']) ? (int)$json['duration_days'] : (isset($_POST['duration_days']) ? (int)$_POST['duration_days'] : 1);

            $contactNumber = preg_replace('/\D+/', '', $contactNumber);

            // Validate duration
            if ($durationDays <= 0) {
                throw new Exception('Reservation duration must be at least 1 day.');
            }

            if (!$itemId) {
                throw new Exception('Item ID is required.');
            }
            if (empty($customerName)) {
                throw new Exception('Customer name is required.');
            }
            if (empty($contactNumber)) {
                throw new Exception('Contact number is required.');
            }
            if (!preg_match('/^\d{11}$/', $contactNumber)) {
                throw new Exception('Contact number must contain exactly 11 digits.');
            }

            $db->beginTransaction();

            $stmtCheck = $db->prepare('SELECT status FROM items WHERE id = ? FOR UPDATE');
            $stmtCheck->execute([$itemId]);
            $item = $stmtCheck->fetch();

            if (!$item) {
                throw new Exception('Item not found.');
            }
            if ($item['status'] !== 'available') {
                throw new Exception('Item is already ' . $item['status'] . '.');
            }

            $stmtEnum = $db->query("SHOW COLUMNS FROM reservations LIKE 'status'")->fetch();
            $statusValue = 'reserved';
            if ($stmtEnum && strpos($stmtEnum['Type'], "'reserved'") === false) {
                $statusValue = 'pending';
            }

            // Calculate expiration date
            $expirationDate = date('Y-m-d H:i:s', strtotime('+' . $durationDays . ' days'));

            $stmt = $db->prepare('INSERT INTO reservations (item_id, customer_name, contact_number, notes, status, duration_days, expiration_date) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$itemId, $customerName, $contactNumber, $notes, $statusValue, $durationDays, $expirationDate]);

            $stmtUpdate = $db->prepare('UPDATE items SET status = ? WHERE id = ?');
            $stmtUpdate->execute(['reserved', $itemId]);

            $db->commit();

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false || $json !== null;
            if ($isAjax) {
                $this->json(['success' => true, 'message' => 'Item successfully reserved!']);
            }

            $this->redirect('/pos');
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false || isset($json);
            if ($isAjax) {
                $this->json(['success' => false, 'message' => $e->getMessage()]);
            }

            die('Reservation Error: ' . $e->getMessage());
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

            $stmt = $db->prepare('SELECT item_id, status FROM reservations WHERE id = ?');
            $stmt->execute([$id]);
            $res = $stmt->fetch();

            if ($res) {
                if (in_array($res['status'], ['reserved', 'pending'], true)) {
                    $stmtUpdate = $db->prepare('UPDATE items SET status = ? WHERE id = ?');
                    $stmtUpdate->execute(['available', $res['item_id']]);
                }

                $stmtDel = $db->prepare('DELETE FROM reservations WHERE id = ?');
                $stmtDel->execute([$id]);
            }

            $db->commit();
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
        }

        $this->redirect('/reservations');
    }

    public function complete() {
        $db = getDB();
        $id = $_POST['id'];

        try {
            $db->beginTransaction();

            $stmt = $db->prepare('SELECT r.*, i.status as item_status FROM reservations r JOIN items i ON r.item_id = i.id WHERE r.id = ?');
            $stmt->execute([$id]);
            $res = $stmt->fetch();

            if (!$res || $res['status'] !== 'paid') {
                throw new Exception('Only paid reservations can be finalized.');
            }

            $stmtComplete = $db->prepare('UPDATE reservations SET status = ? WHERE id = ?');
            $stmtComplete->execute(['completed', $id]);

            if ($res['item_status'] !== 'sold') {
                $stmtItem = $db->prepare('UPDATE items SET status = ? WHERE id = ?');
                $stmtItem->execute(['available', $res['item_id']]);
            }

            $db->commit();
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
        }

        $this->redirect('/reservations');
    }

    public function pay() {
        $db = getDB();
        $id = $_POST['reservation_id'] ?? null;
        $payment_method = $_POST['payment_method'] ?? null;
        $user_id = $_SESSION['user_id'];

        try {
            if (!$id) {
                throw new Exception('Reservation ID is required.');
            }
            if (!in_array($payment_method, ['cash', 'gcash'], true)) {
                throw new Exception('Invalid payment method.');
            }

            $db->beginTransaction();

            $stmt = $db->prepare(
                'SELECT r.*, i.price, i.tag_color, i.id as item_id 
                FROM reservations r 
                JOIN items i ON r.item_id = i.id 
                WHERE r.id = ? AND r.status IN (\'reserved\', \'pending\')'
            );
            $stmt->execute([$id]);
            $res = $stmt->fetch();

            if (!$res) {
                throw new Exception('Reservation not found or already processed.');
            }

            $tag_key = 'discount_' . $res['tag_color'];
            $stmtDisc = $db->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
            $stmtDisc->execute([$tag_key]);
            $discount_rate = (float) $stmtDisc->fetchColumn();

            $discount_amount = $res['price'] * $discount_rate;
            $final_price = $res['price'] - $discount_amount;

            $salesColumns = $db->query("SHOW COLUMNS FROM sales LIKE 'status'")->fetch();
            if ($salesColumns) {
                $stmtSale = $db->prepare('INSERT INTO sales (user_id, total_amount, payment_method, status) VALUES (?, ?, ?, ?)');
                $stmtSale->execute([$user_id, $final_price, $payment_method, 'paid']);
            } else {
                $stmtSale = $db->prepare('INSERT INTO sales (user_id, total_amount, payment_method) VALUES (?, ?, ?)');
                $stmtSale->execute([$user_id, $final_price, $payment_method]);
            }
            $sale_id = $db->lastInsertId();

            $stmtSaleItem = $db->prepare('INSERT INTO sale_items (sale_id, item_id, price, discount, final_price) VALUES (?, ?, ?, ?, ?)');
            $stmtSaleItem->execute([$sale_id, $res['item_id'], $res['price'], $discount_amount, $final_price]);

            $stmtItem = $db->prepare('UPDATE items SET status = ? WHERE id = ?');
            $stmtItem->execute(['sold', $res['item_id']]);

            $stmtRes = $db->prepare('UPDATE reservations SET status = ? WHERE id = ?');
            $stmtRes->execute(['paid', $id]);

            $db->commit();
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            die($e->getMessage());
        }

        $this->redirect('/reservations');
    }

    public function cancel() {
        $db = getDB();
        $id = $_POST['id'] ?? null;

        if (!$id) {
            $this->redirect('/reservations');
        }

        $stmt = $db->prepare('SELECT item_id, status FROM reservations WHERE id = ?');
        $stmt->execute([$id]);
        $res = $stmt->fetch();

        if ($res && in_array($res['status'], ['reserved', 'pending'], true)) {
            $db->beginTransaction();
            $stmtCancel = $db->prepare('UPDATE reservations SET status = ? WHERE id = ?');
            $stmtCancel->execute(['cancelled', $id]);
            $stmtItem = $db->prepare('UPDATE items SET status = ? WHERE id = ?');
            $stmtItem->execute(['available', $res['item_id']]);
            $db->commit();
        }

        $this->redirect('/reservations');
    }
}
