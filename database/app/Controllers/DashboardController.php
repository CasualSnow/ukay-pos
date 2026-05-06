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
        
        // Accurate Inventory Unit Counts
        $inventoryCounts = $db->query("
            SELECT 
                (SELECT SUM(stock) FROM items WHERE status = 'available' AND is_deleted = 0) as available,
                (SELECT SUM(stock) FROM items WHERE status = 'reserved' AND is_deleted = 0) as reserved,
                (SELECT SUM(item_count) FROM sales WHERE status = 'paid') as sold,
                (SELECT SUM(stock) FROM items WHERE is_deleted = 0) as total
        ")->fetch();

        // Ensure we handle NULL if table is empty
        $inventoryCounts['available'] = (int)($inventoryCounts['available'] ?? 0);
        $inventoryCounts['sold'] = (int)($inventoryCounts['sold'] ?? 0);
        $inventoryCounts['reserved'] = (int)($inventoryCounts['reserved'] ?? 0);
        $inventoryCounts['total'] = (int)($inventoryCounts['total'] ?? 0);
        
        // Recent Sales
        $recentSales = $db->query("SELECT s.*, u.username FROM sales s JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC LIMIT 5")->fetchAll();
        
        // Sales by Category
        $salesByCategory = $db->query("SELECT i.category, COUNT(si.id) as count FROM sale_items si JOIN items i ON si.item_id = i.id GROUP BY i.category")->fetchAll();

        // Daily Earnings for Calendar
        $dailyEarnings = $db->query("
            SELECT DATE(created_at) as date, SUM(COALESCE(bargained_price, total_amount)) as total 
            FROM sales 
            GROUP BY DATE(created_at)
        ")->fetchAll();

        $this->view('admin/dashboard', [
            'stats' => [
                'today' => $salesToday['total'] ?? 0,
                'total' => $totalSales['total'] ?? 0,
                'items_sold' => $inventoryCounts['sold'],
                'available' => $inventoryCounts['available'],
                'reserved' => $inventoryCounts['reserved'],
                'total_items' => $inventoryCounts['total']
            ],
            'recentSales' => $recentSales,
            'categoryStats' => $salesByCategory,
            'dailyEarnings' => $dailyEarnings
        ]);
    }
}
