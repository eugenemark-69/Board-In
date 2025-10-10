<?php
require_once __DIR__ . '/../config/config.php';
echo "DB connected.\n";
$res = $conn->query('SELECT COUNT(*) AS c FROM users');
if ($res) {
    $row = $res->fetch_assoc();
    echo "Users: " . ($row['c'] ?? 0) . "\n";
} else {
    echo "Query failed: " . $conn->error . "\n";
}

?>
