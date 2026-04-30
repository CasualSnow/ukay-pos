<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();
    
    // Add contact_number if not exists
    $db->exec("ALTER TABLE reservations ADD COLUMN IF NOT EXISTS contact_number VARCHAR(20) AFTER customer_name");
    
    // Add notes if not exists
    $db->exec("ALTER TABLE reservations ADD COLUMN IF NOT EXISTS notes TEXT AFTER contact_number");
    
    echo "Database schema updated successfully!";
} catch (Exception $e) {
    echo "Error updating database: " . $e->getMessage();
}
