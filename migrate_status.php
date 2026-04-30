<?php
require_once __DIR__ . '/config/database.php';
$db = getDB();
try {
    $db->exec("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'paid', 'completed', 'cancelled') DEFAULT 'pending'");
    echo "Database updated successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
