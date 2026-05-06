<?php
require_once __DIR__ . '/../Models/Item.php';

class InventoryController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            $this->redirect('/');
        }
    }

    public function index() {
        $itemModel = new Item();
        $items = $itemModel->getAll();
        $categories = $itemModel->getCategories();
        $this->view('admin/inventory', ['items' => $items, 'categories' => $categories]);
    }

    public function add() {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO items (name, category, gender, size, price, stock, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['category'],
            $_POST['gender'] ?? 'Unisex',
            $_POST['size'] ?? '',
            $_POST['price'],
            $_POST['stock'] ?? 1,
            'available'
        ]);
        $this->redirect('/inventory');
    }

    public function bulkAdd() {
        $db = getDB();
        $items = $_POST['items']; // Expected to be an array of item objects
        
        $stmt = $db->prepare("INSERT INTO items (name, category, gender, size, price, stock, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($items as $item) {
            if (empty($item['name'])) continue;
            $stmt->execute([
                $item['name'],
                $item['category'],
                $item['gender'] ?? 'Unisex',
                $item['size'] ?? '',
                $item['price'],
                $item['stock'] ?? 1,
                'available'
            ]);
        }
        
        echo json_encode(['success' => true]);
    }

    public function update() {
        $db = getDB();
        
        $stmt = $db->prepare("UPDATE items SET name = ?, category = ?, gender = ?, size = ?, price = ?, stock = ?, status = ? WHERE id = ?");
        $stmt->execute([
            $_POST['name'],
            $_POST['category'],
            $_POST['gender'],
            $_POST['size'],
            $_POST['price'],
            $_POST['stock'],
            $_POST['status'],
            $_POST['id']
        ]);
        $this->redirect('/inventory');
    }

    public function delete() {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM items WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $this->redirect('/inventory');
    }
}
