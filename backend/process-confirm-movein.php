<?php
/*
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['landlord','admin']);

$booking_id = intval($_POST['booking_id'] ?? 0);
if ($booking_id <= 0) {
    flash('error', 'Invalid request');
    header('Location: /board-in/bh_manager/manage-bookings.php');
    exit;
}

$stmt = $conn->prepare('UPDATE bookings SET booking_status = ?, landlord_confirmed_at = NOW() WHERE id = ?');
$status = 'active';
$stmt->bind_param('si', $status, $booking_id);
$stmt->execute();

flash('success', 'Booking confirmed (active)');
header('Location: /board-in/bh_manager/manage-bookings.php');
exit;
*/