<?php
require_once __DIR__ . '/config/database.php';
$db = getDB();
try {
    $db->exec("ALTER TABLE reservations MODIFY COLUMN status ENUM('reserved', 'paid', 'completed', 'cancelled') NOT NULL DEFAULT 'reserved'");
    $db->exec("UPDATE reservations SET status = 'reserved' WHERE status = 'pending'");
    echo "Database updated successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
