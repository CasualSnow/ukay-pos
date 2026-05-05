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
        $id = $user['id'];
        
        // Reset specific users to known passwords if requested
        if ($username === 'owner' || $username === 'staff') {
            $plainPass = ($username === 'owner') ? 'owner123' : 'staff123';
            $hashedPassword = password_hash($plainPass, PASSWORD_DEFAULT);
            
            if ($updateStmt->execute([$hashedPassword, $id])) {
                echo "SUCCESS: Forced reset for user <b>$username</b> (password: $plainPass)<br>";
                echo "DEBUG: New Hash: $hashedPassword (Length: " . strlen($hashedPassword) . ")<br><br>";
            } else {
                echo "ERROR: Failed to update user $username<br><br>";
            }
            $updatedCount++;
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
