<?php
require_once __DIR__ . '/../Models/Item.php';

class PosController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
    }

    public function index() {
        $itemModel = new Item();
        $categories = $itemModel->getCategories();
        $this->view('pos/index', ['categories' => $categories]);
    }

    public function getItems() {
        $category = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? null;
        
        $itemModel = new Item();
        $items = $itemModel->getAll($category, $search);
        
        $this->json($items);
    }

    public function checkout() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $this->json(['success' => false, 'message' => 'Invalid data']);
        }

        $items = $data['items'];
        $total = $data['total'];
        $paymentMethod = $data['payment_method'];
        $cashReceived = $data['cash_received'] ?? null;
        $change = $data['change'] ?? null;

        $db = getDB();
        try {
            $db->beginTransaction();

            // Insert Sale
            $stmt = $db->prepare("INSERT INTO sales (user_id, total_amount, payment_method, cash_received, `change`) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $total, $paymentMethod, $cashReceived, $change]);
            $saleId = $db->lastInsertId();

            // Insert Sale Items and update item status
            $stmtItem = $db->prepare("INSERT INTO sale_items (sale_id, item_id, price, discount, final_price) VALUES (?, ?, ?, ?, ?)");
            $stmtUpdate = $db->prepare("UPDATE items SET status = 'sold' WHERE id = ?");

            foreach ($items as $item) {
                $stmtItem->execute([$saleId, $item['id'], $item['price'], $item['discount'], $item['final_price']]);
                $stmtUpdate->execute([$item['id']]);
            }

            $db->commit();
            $this->json(['success' => true, 'sale_id' => $saleId]);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
