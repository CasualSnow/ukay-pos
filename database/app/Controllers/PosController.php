<?php
require_once __DIR__ . '/../Models/Item.php';

class PosController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
    }

    private function expireReservations() {
        $db = getDB();
        $stmtCheckExpire = $db->query("SHOW COLUMNS FROM reservations LIKE 'expiration_date'");
        $hasExpiration = $stmtCheckExpire->fetch() !== false;
        if (!$hasExpiration) {
            return;
        }

        $stmt = $db->prepare(
            "SELECT r.id, r.item_id
            FROM reservations r
            WHERE r.status IN ('reserved', 'pending')
            AND r.expiration_date IS NOT NULL
            AND r.expiration_date < NOW()"
        );
        $stmt->execute();
        $expiredReservations = $stmt->fetchAll();

        foreach ($expiredReservations as $res) {
            try {
                $db->beginTransaction();
                $stmtUpdateRes = $db->prepare('UPDATE reservations SET status = ? WHERE id = ?');
                $stmtUpdateRes->execute(['expired', $res['id']]);

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
        $itemModel = new Item();
        $categories = $itemModel->getCategories();
        $this->view('pos/index', ['categories' => $categories]);
    }

    public function getItems() {
        $this->expireReservations();

        $category = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? null;
        
        $itemModel = new Item();
        $items = $itemModel->getAll($category, $search);
        
        $this->json($items);
    }

    public function checkout() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['items'], $data['total'], $data['payment_method'])) {
            $this->json(['success' => false, 'message' => 'Invalid checkout data.']);
        }

        $items = $data['items'];
        $total = (float) $data['total'];
        $bargainedPrice = isset($data['bargained_price']) && $data['bargained_price'] !== '' ? (float) $data['bargained_price'] : null;
        $finalTotal = $bargainedPrice !== null ? $bargainedPrice : $total;
        $paymentMethod = $data['payment_method'];
        $cashReceived = isset($data['cash_received']) ? (float) $data['cash_received'] : null;
        $change = isset($data['change']) ? (float) $data['change'] : null;
        $proofOfPurchase = $data['proof_of_purchase'] ?? null;

        if (!is_array($items) || count($items) === 0) {
            $this->json(['success' => false, 'message' => 'Cart cannot be empty.']);
        }
        if (!in_array($paymentMethod, ['cash', 'gcash'], true)) {
            $this->json(['success' => false, 'message' => 'Invalid payment method.']);
        }
        if ($paymentMethod === 'cash' && ($cashReceived === null || round($cashReceived, 2) < round($finalTotal, 2))) {
            $this->json(['success' => false, 'message' => 'Cash received must cover the total amount.']);
        }

        $db = getDB();
        try {
            // Error logging for debugging
            error_log("Processing checkout: " . json_encode($data));

            $db->beginTransaction();

            $stmtCheck = $db->prepare('SELECT status, stock FROM items WHERE id = ? FOR UPDATE');
            $stmtInsertSale = $db->prepare('INSERT INTO sales (user_id, total_amount, bargained_price, payment_method, status, cash_received, `change`, proof_of_purchase, item_count) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            
            $itemCount = 0;
            foreach ($items as $item) {
                $itemCount += (int)$item['quantity'];
            }

            $stmtInsertSale->execute([
                $_SESSION['user_id'], 
                (float)$total, 
                $bargainedPrice !== null ? (float)$bargainedPrice : null,
                $paymentMethod, 
                'paid', 
                $cashReceived !== null ? (float)$cashReceived : null, 
                $change !== null ? (float)$change : null, 
                $proofOfPurchase,
                (int)$itemCount
            ]);
            $saleId = $db->lastInsertId();

            $stmtInsertItem = $db->prepare('INSERT INTO sale_items (sale_id, item_id, price, discount, final_price) VALUES (?, ?, ?, ?, ?)');
            $stmtUpdateItem = $db->prepare('UPDATE items SET stock = stock - ?, status = CASE WHEN stock - ? <= 0 THEN "sold" ELSE status END WHERE id = ?');

            foreach ($items as $item) {
                $itemId = $item['id'];
                $quantity = (int)$item['quantity'];
                
                $stmtCheck->execute([$itemId]);
                $storedItem = $stmtCheck->fetch();
                
                if (!$storedItem) {
                    throw new Exception('Item ID ' . $itemId . ' not found.');
                }
                
                if ($storedItem['status'] !== 'available' && $storedItem['status'] !== 'reserved') {
                    throw new Exception('Item ' . $item['name'] . ' is already ' . $storedItem['status']);
                }

                if ($storedItem['stock'] < $quantity) {
                    throw new Exception('Not enough stock for ' . $item['name']);
                }

                $price = (float)$item['price'];
                
                // Insert individual items into sale_items
                for ($i = 0; $i < $quantity; $i++) {
                    $stmtInsertItem->execute([$saleId, $itemId, $price, 0, $price]);
                }
                
                // Update item stock and status
                $stmtUpdateItem->execute([$quantity, $quantity, $itemId]);
            }

            $db->commit();
            
            if (ob_get_level()) ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'sale_id' => $saleId]);
            exit;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Checkout Error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function uploadCapture() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['image'])) {
            $this->json(['success' => false, 'message' => 'No image data']);
        }

        $img = $data['image'];
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $fileData = base64_decode($img);
        
        $uploadDir = __DIR__ . '/../../../public/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $fileName = 'capture_' . uniqid() . '.png';
        $filePath = $uploadDir . $fileName;
        
        if (file_put_contents($filePath, $fileData)) {
            $this->json(['success' => true, 'file_url' => '/uploads/' . $fileName]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to save image']);
        }
    }
}
