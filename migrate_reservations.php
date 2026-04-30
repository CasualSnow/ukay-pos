<?php
require_once __DIR__ . '/config/database.php';

$db = getDB();

try {
    $db->beginTransaction();

    // 1. Add duration_days column
    $stmtCheckDuration = $db->query("SHOW COLUMNS FROM reservations LIKE 'duration_days'");
    if (!$stmtCheckDuration->fetch()) {
        $db->exec("ALTER TABLE reservations ADD COLUMN duration_days INT UNSIGNED NOT NULL DEFAULT 1");
        echo "✓ Added duration_days column\n";
    } else {
        echo "- duration_days column already exists\n";
    }

    // 2. Add expiration_date column
    $stmtCheckExpiration = $db->query("SHOW COLUMNS FROM reservations LIKE 'expiration_date'");
    if (!$stmtCheckExpiration->fetch()) {
        $db->exec("ALTER TABLE reservations ADD COLUMN expiration_date DATETIME");
        echo "✓ Added expiration_date column\n";
    } else {
        echo "- expiration_date column already exists\n";
    }

    // 3. Update status enum to include 'expired'
    $stmtEnum = $db->query("SHOW COLUMNS FROM reservations LIKE 'status'");
    $enumRow = $stmtEnum->fetch();
    if ($enumRow && strpos($enumRow['Type'], "'expired'") === false) {
        $db->exec("ALTER TABLE reservations MODIFY COLUMN status ENUM('reserved', 'paid', 'completed', 'cancelled', 'expired') NOT NULL DEFAULT 'reserved'");
        echo "✓ Updated status enum to include 'expired'\n";
    } else {
        echo "- status enum already includes 'expired'\n";
    }

    $db->commit();
    echo "\n✅ Migration completed successfully!\n";
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
}
