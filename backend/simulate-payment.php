<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user'])) {
    flash('error', 'Please login to complete payment');
    header('Location: /board-in/user/login.php');
    exit;
}

$booking_id = intval($_POST['booking_id'] ?? 0);
$txid = trim($_POST['txid'] ?? '');

if ($booking_id <= 0 || empty($txid)) {
    flash('error', 'Invalid payment');
    header('Location: /board-in/student/my-bookings.php');
    exit;
}

$stmt = $conn->prepare('SELECT b.*, bh.manager_id FROM bookings b LEFT JOIN boarding_houses bh ON bh.id = b.boarding_house_id WHERE b.id = ? LIMIT 1');
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$res = $stmt->get_result();
$b = $res->fetch_assoc();
if (!$b) {
    flash('error', 'Booking not found');
    header('Location: /board-in/student/my-bookings.php');
    exit;
}

// update booking as paid
$stmt2 = $conn->prepare('UPDATE bookings SET payment_status = ?, payment_reference = ?, booking_status = ? WHERE id = ?');
$paid = 'paid';
$booking_status = 'paid';
$stmt2->bind_param('sssi', $paid, $txid, $booking_status, $booking_id);
$stmt2->execute();

// update or insert transaction
$stmt3 = $conn->prepare('UPDATE transactions SET status = ?, payment_reference = ?, processed_at = NOW() WHERE booking_id = ? AND transaction_type = "booking_payment"');
$done = 'completed';
$stmt3->bind_param('ssi', $done, $txid, $booking_id);
$stmt3->execute();
if ($conn->affected_rows === 0) {
    $stmt4 = $conn->prepare('INSERT INTO transactions (booking_id, transaction_type, amount, payment_method, payment_reference, status, processed_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
    $type = 'booking_payment';
    $amount = $b['total_amount'];
    $method = 'GCash';
    $stmt4->bind_param('isdsss', $booking_id, $type, $amount, $method, $txid, $done);
    $stmt4->execute();
}

// increase landlord commission owed
$commission = floatval($b['commission_amount']);
if ($commission > 0 && $b['manager_id']) {
    $stmt5 = $conn->prepare('UPDATE landlords SET commission_owed = commission_owed + ? WHERE user_id = ?');
    $stmt5->bind_param('di', $commission, $b['manager_id']);
    $stmt5->execute();
}

header('Location: /board-in/student/booking-confirmation.php?id=' . $booking_id);
exit;
