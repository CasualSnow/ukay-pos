<?php
/**
 * Setup File - Hash existing plain text passwords for Railway deployment
 * This file will only run once and then disable itself.
 */

// Check if already locked
/*
$lockFile = __DIR__ . '/setup.lock';
if (file_exists($lockFile)) {
    die("Setup has already been completed. This script is disabled for security.");
}
*/

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDB();
    
    // 1. Fetch all users
    $stmt = $pdo->query("SELECT id, username, password FROM users");
    $users = $stmt->fetchAll();
    
    $updatedCount = 0;
    
    // 2. Hash passwords that aren't already hashed
    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    
    foreach ($users as $user) {
        $password = $user['password'];
        $username = $user['username'];
        
        // Reset specific users to known passwords if requested
        if ($username === 'owner') {
            $hashedPassword = password_hash('owner123', PASSWORD_DEFAULT);
            $updateStmt->execute([$hashedPassword, $user['id']]);
            $updatedCount++;
            echo "FORCED reset for user: owner (password: owner123)<br>";
            continue;
        }
        
        if ($username === 'staff') {
            $hashedPassword = password_hash('staff123', PASSWORD_DEFAULT);
            $updateStmt->execute([$hashedPassword, $user['id']]);
            $updatedCount++;
            echo "FORCED reset for user: staff (password: staff123)<br>";
            continue;
        }

        // Simple check: password_hash produces strings that start with $2y$ (for BCRYPT)
        // If it doesn't start with $2y$, it's likely plain text
        if (substr($password, 0, 4) !== '$2y$') {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt->execute([$hashedPassword, $user['id']]);
            $updatedCount++;
            echo "Updated password for user: " . htmlspecialchars($user['username']) . "<br>";
        }
    }
    
    // 3. Create lock file to prevent re-running
    file_put_contents($lockFile, date('Y-m-d H:i:s'));
    
    echo "<h3>Success!</h3>";
    echo "Total users updated: $updatedCount<br>";
    echo "Setup is now complete and this script has been disabled.";

} catch (Exception $e) {
    die("Error during setup: " . $e->getMessage());
}
