<?php
require_once __DIR__ . '/../Models/Item.php';

class DashboardController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            $this->redirect('/');
        }
    }

    public function index() {
        $db = getDB();
        
        // Sales Stats
        $salesToday = $db->query("SELECT SUM(total_amount) as total FROM sales WHERE DATE(created_at) = CURDATE()")->fetch();
        $totalSales = $db->query("SELECT SUM(total_amount) as total FROM sales")->fetch();
        
        // Accurate Inventory Counts from the same source of truth
        $inventoryCounts = $db->query("
            SELECT 
                COUNT(CASE WHEN status = 'available' THEN 1 END) as available,
                COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold,
                COUNT(CASE WHEN status = 'reserved' THEN 1 END) as reserved,
                COUNT(*) as total
            FROM items
        ")->fetch();
        
        // Recent Sales
        $recentSales = $db->query("SELECT s.*, u.username FROM sales s JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC LIMIT 5")->fetchAll();
        
        // Sales by Category
        $salesByCategory = $db->query("SELECT i.category, COUNT(si.id) as count FROM sale_items si JOIN items i ON si.item_id = i.id GROUP BY i.category")->fetchAll();

        $this->view('admin/dashboard', [
            'stats' => [
                'today' => $salesToday['total'] ?? 0,
                'total' => $totalSales['total'] ?? 0,
                'items_sold' => $inventoryCounts['sold'] ?? 0,
                'available' => $inventoryCounts['available'] ?? 0,
                'reserved' => $inventoryCounts['reserved'] ?? 0,
                'total_items' => $inventoryCounts['total'] ?? 0
            ],
            'recentSales' => $recentSales,
            'categoryStats' => $salesByCategory
        ]);
    }
}
