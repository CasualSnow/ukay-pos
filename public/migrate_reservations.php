<?php
require_once __DIR__ . '/../config/database.php';

echo "<!DOCTYPE html><html><head><title>Reservation Migration</title></head><body style='font-family:Arial,Helvetica,sans-serif;padding:2rem;'>";
echo "<h1>Reservation System Migration</h1>";

$db = getDB();

try {
    $db->beginTransaction();
    echo "<ul style='line-height:2;'>";

    // 1. Add duration_days column
    $stmtCheckDuration = $db->query("SHOW COLUMNS FROM reservations LIKE 'duration_days'");
    if (!$stmtCheckDuration->fetch()) {
        $db->exec("ALTER TABLE reservations ADD COLUMN duration_days INT UNSIGNED NOT NULL DEFAULT 1");
        echo "<li style='color:green;'>✅ Added duration_days column</li>";
    } else {
        echo "<li style='color:gray;'>ℹ️ duration_days column already exists</li>";
    }

    // 2. Add expiration_date column
    $stmtCheckExpiration = $db->query("SHOW COLUMNS FROM reservations LIKE 'expiration_date'");
    if (!$stmtCheckExpiration->fetch()) {
        $db->exec("ALTER TABLE reservations ADD COLUMN expiration_date DATETIME");
        echo "<li style='color:green;'>✅ Added expiration_date column</li>";
    } else {
        echo "<li style='color:gray;'>ℹ️ expiration_date column already exists</li>";
    }

    // 3. Update status enum to include 'expired'
    $stmtEnum = $db->query("SHOW COLUMNS FROM reservations LIKE 'status'");
    $enumRow = $stmtEnum->fetch();
    if ($enumRow && strpos($enumRow['Type'], "'expired'") === false) {
        $db->exec("ALTER TABLE reservations MODIFY COLUMN status ENUM('reserved', 'paid', 'completed', 'cancelled', 'expired') NOT NULL DEFAULT 'reserved'");
        echo "<li style='color:green;'>✅ Updated status enum to include 'expired'</li>";
    } else {
        echo "<li style='color:gray;'>ℹ️ status enum already includes 'expired'</li>";
    }

    $db->commit();
    echo "</ul>";
    echo "<h2 style='color:green;'>✅ Migration completed successfully!</h2>";
    echo "<p><a href='/thrift_pos/dashboard'>Go to Dashboard</a></p>";
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "<h2 style='color:red;'>❌ Migration failed: " . htmlspecialchars($e->getMessage()) . "</h2>";
}

echo "</body></html>";
