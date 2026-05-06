<?php

class ReportController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            $this->redirect('/');
        }
    }

    public function index() {
        $db = getDB();
        
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        // Sales Report (Daily filtered by date)
        $stmt = $db->prepare("
            SELECT DATE(created_at) as date, SUM(COALESCE(bargained_price, total_amount)) as total, COUNT(*) as count 
            FROM sales 
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at) 
            ORDER BY date DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        $dailySales = $stmt->fetchAll();

        $this->view('admin/reports', [
            'dailySales' => $dailySales,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
}
