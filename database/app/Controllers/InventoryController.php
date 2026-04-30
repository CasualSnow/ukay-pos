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
        $image_url = 'https://picsum.photos/400/400?random=' . rand(1, 1000);
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../../public/assets/images/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = '/thrift_pos/assets/images/' . $file_name;
            }
        }

        $db = getDB();
        $stmt = $db->prepare("INSERT INTO items (name, category, price, tag_color, image_url, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['category'],
            $_POST['price'],
            $_POST['tag_color'],
            $image_url,
            'available'
        ]);
        $this->redirect('/inventory');
    }

    public function update() {
        $db = getDB();
        
        $image_sql = "";
        $params = [
            $_POST['name'],
            $_POST['category'],
            $_POST['price'],
            $_POST['tag_color'],
            $_POST['status']
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../../public/assets/images/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = '/thrift_pos/assets/images/' . $file_name;
                $image_sql = ", image_url = ?";
                $params[] = $image_url;
            }
        }

        $params[] = $_POST['id'];
        $stmt = $db->prepare("UPDATE items SET name = ?, category = ?, price = ?, tag_color = ?, status = ? $image_sql WHERE id = ?");
        $stmt->execute($params);
        $this->redirect('/inventory');
    }

    public function delete() {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM items WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $this->redirect('/inventory');
    }
}
