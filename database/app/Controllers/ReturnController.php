<?php

class ReturnController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
    }

    public function index() {
        $db = getDB();
        // Get last 20 sales for return selection
        $sales = $db->query("
            SELECT s.*, u.username 
            FROM sales s 
            JOIN users u ON s.user_id = u.id 
            ORDER BY s.created_at DESC 
            LIMIT 20
        ")->fetchAll();
        
        $this->view('pos/returns', ['sales' => $sales]);
    }

    public function getSaleItems($id) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT si.*, i.name, i.image_url 
            FROM sale_items si 
            LEFT JOIN items i ON si.item_id = i.id 
            WHERE si.sale_id = ?
        ");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll();
        $this->json($items);
    }

    public function process() {
        $db = getDB();
        $itemId = $_POST['item_id'];
        $saleItemId = $_POST['sale_item_id'];
        
        try {
            $db->beginTransaction();
            
            // 1. Restore item status to 'available'
            $stmt1 = $db->prepare("UPDATE items SET status = 'available' WHERE id = ?");
            $stmt1->execute([$itemId]);
            
            // 2. Remove from sale_items or mark as returned
            // For now, let's just delete the record from sale_items to keep it simple and fix the UI
            $stmt2 = $db->prepare("DELETE FROM sale_items WHERE id = ?");
            $stmt2->execute([$saleItemId]);
            
            $db->commit();
            $this->redirect('/returns');
        } catch (Exception $e) {
            $db->rollBack();
            die($e->getMessage());
        }
    }
}
