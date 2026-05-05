<?php
/**
 * COMPLETE DATABASE SYNCHRONIZATION - RUN THIS ON RAILWAY
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

echo "<h2>Railway Database Sync Tool</h2>";

try {
    $pdo = getDB();
    echo "✅ Connected to Database: " . getenv('MYSQLDATABASE') . "<br><hr>";
    
    // 1. Create/Update Tables based on database.sql
    echo "<b>Synchronizing tables...</b><br>";
    
    $queries = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            fullname VARCHAR(100),
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'staff') NOT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            theme ENUM('light', 'dark') DEFAULT 'light',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            category VARCHAR(50) NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            tag_color ENUM('red', 'blue', 'green', 'yellow') NOT NULL,
            image_url VARCHAR(255),
            status ENUM('available', 'sold', 'reserved') DEFAULT 'available',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS sales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            total_amount DECIMAL(10, 2) NOT NULL,
            payment_method ENUM('cash', 'gcash') NOT NULL,
            status ENUM('paid', 'pending', 'cancelled') NOT NULL DEFAULT 'paid',
            cash_received DECIMAL(10, 2),
            `change` DECIMAL(10, 2),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )",
        "CREATE TABLE IF NOT EXISTS sale_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sale_id INT,
            item_id INT,
            price DECIMAL(10, 2) NOT NULL,
            discount DECIMAL(10, 2) DEFAULT 0,
            final_price DECIMAL(10, 2) NOT NULL,
            FOREIGN KEY (sale_id) REFERENCES sales(id),
            FOREIGN KEY (item_id) REFERENCES items(id)
        )",
        "CREATE TABLE IF NOT EXISTS reservations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            item_id INT,
            customer_name VARCHAR(100) NOT NULL,
            contact_number VARCHAR(20),
            notes TEXT,
            duration_days INT DEFAULT 1,
            expiration_date DATETIME,
            status ENUM('reserved', 'pending', 'paid', 'completed', 'cancelled', 'expired') NOT NULL DEFAULT 'reserved',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (item_id) REFERENCES items(id)
        )",
        "CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(50) UNIQUE,
            setting_value VARCHAR(255)
        )"
    ];

    foreach ($queries as $query) {
        $pdo->exec($query);
    }
    echo "✅ Tables created or verified.<br>";

    // 2. Ensure specific columns exist
    echo "Checking for missing columns...<br>";
    
    $fixes = [
        'sales' => [
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP', 
            'user_id' => 'INT',
            'cash_received' => 'DECIMAL(10, 2)',
            'change' => 'DECIMAL(10, 2)',
            'item_count' => 'INT DEFAULT 1'
        ],
        'users' => ['created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP', 'fullname' => 'VARCHAR(100)', 'status' => "ENUM('active', 'inactive') DEFAULT 'active'", 'theme' => "ENUM('light', 'dark') DEFAULT 'light'"],
        'items' => ['created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'],
        'reservations' => ['created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP', 'duration_days' => 'INT DEFAULT 1', 'expiration_date' => 'DATETIME', 'item_id' => 'INT']
    ];

    foreach ($fixes as $table => $columns) {
        foreach ($columns as $col => $definition) {
            $check = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$col'")->fetch();
            if (!$check) {
                echo "Adding '$col' to $table...<br>";
                $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$col` $definition");
                echo "✅ Added '$col' to $table.<br>";
            } else {
                echo "ℹ️ '$col' already exists in $table.<br>";
            }
        }
    }

    // 3. Ensure your user exists
    echo "Syncing users...<br>";
    $checkUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $checkUser->execute(['ian']);
    if (!$checkUser->fetch()) {
        $pdo->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, ?, ?)")
            ->execute(['ian', 'ian123', 'admin', 'active']);
        echo "✅ User 'ian' created.<br>";
    } else {
        echo "ℹ️ User 'ian' already exists.<br>";
    }

    echo "<hr><h3>Sync Complete!</h3>";
    echo "You can now go back to the <a href='/dashboard'>Dashboard</a>.";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "<br>";
}
?>
