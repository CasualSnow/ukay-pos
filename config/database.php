<?php
define('DB_HOST', 'trolley.proxy.rlwy.net');
define('DB_NAME', 'railway');
define('DB_USER', 'root');
define('DB_PASS', 'WqmvjmSnfHfzrKyOxFwdjStVfqhjIdey');
define('DB_PORT', '53939');

function getDB() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>