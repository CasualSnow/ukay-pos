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
            $db->beginTransaction();

            $stmtCheck = $db->prepare('SELECT status, stock FROM items WHERE id = ? FOR UPDATE');
            $stmtInsertSale = $db->prepare('INSERT INTO sales (user_id, total_amount, bargained_price, payment_method, status, cash_received, `change`, proof_of_purchase, item_count) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            
            $itemCount = 0;
            foreach ($items as $item) {
                $itemCount += $item['quantity'];
            }

            $stmtInsertSale->execute([
                $_SESSION['user_id'], 
                $total, 
                $bargainedPrice,
                $paymentMethod, 
                'paid', 
                $cashReceived, 
                $change, 
                $proofOfPurchase,
                $itemCount
            ]);
            $saleId = $db->lastInsertId();

            $stmtInsertItem = $db->prepare('INSERT INTO sale_items (sale_id, item_id, price, discount, final_price) VALUES (?, ?, ?, ?, ?)');
            $stmtUpdateItem = $db->prepare('UPDATE items SET stock = stock - ?, status = CASE WHEN stock <= 0 THEN "sold" ELSE status END WHERE id = ?');

            foreach ($items as $item) {
                $stmtCheck->execute([$item['id']]);
                $storedItem = $stmtCheck->fetch();
                
                if (!$storedItem || $storedItem['status'] !== 'available' || $storedItem['stock'] < $item['quantity']) {
                    throw new Exception('Item ' . $item['name'] . ' is no longer available in the requested quantity.');
                }

                $price = (float) $item['price'];
                $quantity = (int) $item['quantity'];
                
                // For sale_items, we'll store per unit price. 
                // Since bargained_price is for the whole sale, we don't necessarily need to distribute it to items unless required.
                // But let's store the original price as final_price since discounts are removed.
                for ($i = 0; $i < $quantity; $i++) {
                    $stmtInsertItem->execute([$saleId, $item['id'], $price, 0, $price]);
                }
                
                $stmtUpdateItem->execute([$quantity, $item['id']]);
            }

            $db->commit();
            $this->json(['success' => true, 'sale_id' => $saleId]);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->json(['success' => false, 'message' => $e->getMessage()]);
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
