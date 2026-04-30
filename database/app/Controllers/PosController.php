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
        $paymentMethod = $data['payment_method'];
        $cashReceived = isset($data['cash_received']) ? (float) $data['cash_received'] : null;
        $change = isset($data['change']) ? (float) $data['change'] : null;

        if (!is_array($items) || count($items) === 0) {
            $this->json(['success' => false, 'message' => 'Cart cannot be empty.']);
        }
        if (!in_array($paymentMethod, ['cash', 'gcash'], true)) {
            $this->json(['success' => false, 'message' => 'Invalid payment method.']);
        }
        if ($paymentMethod === 'cash' && ($cashReceived === null || $cashReceived < $total)) {
            $this->json(['success' => false, 'message' => 'Cash received must cover the total amount.']);
        }

        $db = getDB();
        try {
            $db->beginTransaction();

            $stmtCheck = $db->prepare('SELECT status FROM items WHERE id = ? FOR UPDATE');
            $salesColumns = $db->query("SHOW COLUMNS FROM sales LIKE 'status'")->fetch();
            if ($salesColumns) {
                $stmtInsertSale = $db->prepare('INSERT INTO sales (user_id, total_amount, payment_method, status, cash_received, `change`) VALUES (?, ?, ?, ?, ?, ?)');
                $stmtInsertSale->execute([$_SESSION['user_id'], $total, $paymentMethod, 'paid', $cashReceived, $change]);
            } else {
                $stmtInsertSale = $db->prepare('INSERT INTO sales (user_id, total_amount, payment_method, cash_received, `change`) VALUES (?, ?, ?, ?, ?)');
                $stmtInsertSale->execute([$_SESSION['user_id'], $total, $paymentMethod, $cashReceived, $change]);
            }
            $saleId = $db->lastInsertId();

            $calculatedTotal = 0;
            foreach ($items as $item) {
                if (empty($item['id'])) {
                    throw new Exception('Invalid item entry in cart.');
                }

                $stmtCheck->execute([$item['id']]);
                $storedItem = $stmtCheck->fetch();
                if (!$storedItem) {
                    throw new Exception('Item with ID ' . $item['id'] . ' not found.');
                }
                if ($storedItem['status'] !== 'available') {
                    throw new Exception('Item with ID ' . $item['id'] . ' is not available for sale.');
                }

                $price = isset($item['price']) ? (float) $item['price'] : 0;
                $discount = isset($item['discount']) ? (float) $item['discount'] : 0;
                $finalPrice = isset($item['final_price']) ? (float) $item['final_price'] : $price - $discount;

                $expectedFinalPrice = $price - $discount;
                if (abs($finalPrice - $expectedFinalPrice) > 0.01) {
                    $finalPrice = $expectedFinalPrice;
                }

                $calculatedTotal += $finalPrice;

                $stmtInsertItem->execute([$saleId, $item['id'], $price, $discount, $finalPrice]);
                $stmtUpdateItem->execute(['sold', $item['id']]);
            }

            if (abs($calculatedTotal - $total) > 0.01) {
                throw new Exception('Total amount mismatch.');
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
}
