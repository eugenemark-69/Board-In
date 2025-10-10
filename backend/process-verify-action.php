<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['admin']);

$landlord_id = intval($_POST['landlord_id'] ?? 0);
$action = $_POST['action'] ?? '';
$notes = trim($_POST['notes'] ?? '');

if ($landlord_id <= 0 || !in_array($action, ['approve','reject'])) {
    flash('error', 'Invalid request');
    header('Location: /board-in/admin/verify-landlords.php');
    exit;
}

if ($action === 'approve') {
    $stmt = $conn->prepare('UPDATE landlords SET verification_status = ?, verified_at = NOW() WHERE id = ?');
    $status = 'approved';
    $stmt->bind_param('si', $status, $landlord_id);
    $stmt->execute();
    flash('success', 'Landlord approved');
} else {
    $stmt = $conn->prepare('UPDATE landlords SET verification_status = ?, verified_at = NULL WHERE id = ?');
    $status = 'rejected';
    $stmt->bind_param('si', $status, $landlord_id);
    $stmt->execute();
    // store note in a simple way: append to users table via flash or consider separate admin_notes table (not implemented)
    flash('success', 'Landlord rejected');
}

header('Location: /board-in/admin/verify-landlords.php');
exit;
