<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();

    $stmt = $db->query("SHOW COLUMNS FROM reservations LIKE 'contact_number'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE reservations ADD COLUMN contact_number VARCHAR(20) AFTER customer_name");
    }

    $stmt = $db->query("SHOW COLUMNS FROM reservations LIKE 'notes'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE reservations ADD COLUMN notes TEXT AFTER contact_number");
    }

    $db->exec("ALTER TABLE reservations MODIFY COLUMN status ENUM('reserved', 'paid', 'completed', 'cancelled') NOT NULL DEFAULT 'reserved'");
    $db->exec("UPDATE reservations SET status = 'reserved' WHERE status = 'pending'");

    $stmt = $db->query("SHOW COLUMNS FROM sales LIKE 'status'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE sales ADD COLUMN status ENUM('paid', 'pending', 'cancelled') NOT NULL DEFAULT 'paid'");
    }

    echo "Database schema updated successfully!";
} catch (Exception $e) {
    echo "Error updating database: " . $e->getMessage();
}
