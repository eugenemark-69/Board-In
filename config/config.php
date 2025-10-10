<?php
// Database configuration - update with your local credentials
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'boardin');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create MySQLi connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Set charset
$conn->set_charset('utf8mb4');

// helper functions are provided from includes/functions.php

// Debug mode - set to true on development machines to show full errors (DO NOT enable in production)
if (!defined('DEBUG')) define('DEBUG', false);

// Payment / platform settings (set real values in a local config or env)
define('PLATFORM_GCASH_NUMBER', '');
define('PAYMENT_PROVIDER_SECRET', ''); // e.g. PayMongo webhook secret or shared token
define('PLATFORM_COMMISSION_RATE', 0.03); // 3% commission by default

?>
