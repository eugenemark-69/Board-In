<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user'])) {
    flash('error', 'Please login');
    header('Location: /board-in/user/login.php');
    exit;
}

$booking_id = intval($_POST['booking_id'] ?? 0);
$payment_reference = trim($_POST['payment_reference'] ?? '');

if ($booking_id <= 0 || empty($payment_reference)) {
    flash('error', 'Invalid payment reference');
    header('Location: /board-in/student/my-bookings.php');
    exit;
}

// update booking
$stmt = $conn->prepare('UPDATE bookings SET payment_status = ?, payment_reference = ?, booking_status = ? WHERE id = ?');
$paid = 'paid';
$booking_status = 'paid';
$stmt->bind_param('sssi', $paid, $payment_reference, $booking_status, $booking_id);
$stmt->execute();

// update transaction if exists
$stmt2 = $conn->prepare('UPDATE transactions SET payment_reference = ?, status = ? WHERE booking_id = ? AND transaction_type = "booking_payment"');
$tx_status = 'completed';
$stmt2->bind_param('ssi', $payment_reference, $tx_status, $booking_id);
$stmt2->execute();

flash('success', 'Payment reference submitted — booking marked as paid (manual verification may be required).');
header('Location: /board-in/student/my-bookings.php');
exit;
