<?php

class HistoryController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
    }

    public function index() {
        $db = getDB();
        
        // Use today's date if no date is provided
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $stmt = $db->prepare("
            SELECT si.*, s.created_at as sale_date, s.payment_method, i.name as item_name, i.category, u.username as staff_name, s.proof_of_purchase
            FROM sale_items si
            JOIN sales s ON si.sale_id = s.id
            JOIN items i ON si.item_id = i.id
            JOIN users u ON s.user_id = u.id
            WHERE DATE(s.created_at) = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$date]);
        $sales = $stmt->fetchAll();

        // Debug: Log if no sales found for the date
        if (empty($sales)) {
            error_log("No sales found in history for date: " . $date);
        }

        $this->view('admin/history', [
            'sales' => $sales,
            'selectedDate' => $date
        ]);
    }
}
