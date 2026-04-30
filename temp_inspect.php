<?php
include 'config/database.php';
$db = getDB();
foreach ($db->query('SHOW COLUMNS FROM reservations') as $row) {
    echo $row['Field'] . ' ' . $row['Type'] . ' ' . ($row['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . PHP_EOL;
}
echo "---\n";
foreach ($db->query('SELECT id,item_id,customer_name,contact_number,duration_days,expiration_date,created_at,status FROM reservations ORDER BY id DESC LIMIT 10') as $row) {
    echo implode('|', array_map(function($v){ return $v === null ? 'NULL' : $v; }, $row)) . PHP_EOL;
}
