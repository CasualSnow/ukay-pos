<?php
/**
 * EMERGENCY DATABASE FIX - RUN THIS ON RAILWAY
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

echo "<h2>Emergency Railway DB Fix</h2>";

try {
    $pdo = getDB();
    echo "✅ Connected to Database: " . getenv('MYSQLDATABASE') . "<br>";
    
    // 1. Ensure columns exist (just in case they were missed)
    echo "Checking table structure...<br>";
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive') DEFAULT 'active'");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('admin', 'staff') DEFAULT 'admin'");
    
    // 2. FORCE reset the owner account
    echo "Resetting 'owner' account...<br>";
    
    // Delete if exists to avoid UNIQUE constraint issues while resetting
    $pdo->exec("DELETE FROM users WHERE username = 'owner'");
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, ?, ?)");
    // We use PLAIN TEXT for this emergency fix so there is ZERO chance of hash mismatch
    $stmt->execute(['owner', 'owner123', 'admin', 'active']);
    
    echo "✅ <b>User 'owner' has been reset!</b><br>";
    echo "Login with:<br>";
    echo "Username: <b>owner</b><br>";
    echo "Password: <b>owner123</b><br><br>";
    
    // 3. Verify the data actually exists now
    $check = $pdo->query("SELECT * FROM users WHERE username = 'owner'")->fetch();
    if ($check) {
        echo "Data Verification: User found in DB! Current DB Password: " . $check['password'] . "<br>";
    } else {
        echo "❌ ERROR: Data was not saved to DB!<br>";
    }

} catch (Exception $e) {
    echo "❌ DATABASE ERROR: " . $e->getMessage() . "<br>";
    echo "Host: " . getenv('MYSQLHOST') . "<br>";
}
?>
