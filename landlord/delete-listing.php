<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

$db = getDB();

// Check if ID is provided
if (!isset($_GET['id'])) {
    die("No listing ID provided.");
}

$id = intval($_GET['id']);

// Delete listing
$stmt = $db->prepare("DELETE FROM listings WHERE id = ?");
$stmt->execute([$id]);

// Redirect back
header("Location: my-listings.php");
exit;
?>
