<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user'])) {
	flash('error', 'Please login to book');
	header('Location: /board-in/user/login.php');
	exit;
}

$student_id = $_SESSION['user']['id'];
$listing_id = intval($_POST['listing_id'] ?? 0);
$move_in_date = $_POST['move_in_date'] ?? null;

if ($listing_id <= 0 || empty($move_in_date)) {
	flash('error', 'Invalid booking request');
	header('Location: /board-in/pages/search.php');
	exit;
}

$stmt = $conn->prepare('SELECT id, monthly_rent, security_deposit, manager_id FROM boarding_houses WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $listing_id);
$stmt->execute();
$res = $stmt->get_result();
$listing = $res->fetch_assoc();
if (!$listing) {
	flash('error', 'Listing not found');
	header('Location: /board-in/pages/search.php');
	exit;
}

$monthly = floatval($listing['monthly_rent']);
$deposit = floatval($listing['security_deposit']);
$total = $monthly + $deposit;
$commission = round($total * 0.03, 2);

// create booking
$ref = 'BK-' . date('Y') . '-' . str_pad(rand(1,9999), 4, '0', STR_PAD_LEFT);
$stmt2 = $conn->prepare('INSERT INTO bookings (booking_reference, student_id, boarding_house_id, landlord_id, move_in_date, monthly_rent, security_deposit, total_amount, commission_amount, payment_status, booking_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
$landlord_id = $listing['manager_id'];
$payment_status = 'pending';
$booking_status = 'pending';
$stmt2->bind_param('siiisdddddss', $ref, $student_id, $listing_id, $landlord_id, $move_in_date, $monthly, $deposit, $total, $commission, $payment_status, $booking_status);
if (!$stmt2->execute()) {
	flash('error', 'Failed to create booking');
	header('Location: /board-in/pages/listing.php?id=' . $listing_id);
	exit;
}

$booking_id = $conn->insert_id;

// create transaction stub (status pending)
$stmt3 = $conn->prepare('INSERT INTO transactions (booking_id, transaction_type, amount, payment_method, payment_reference, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
$tx_type = 'booking_payment';
$payment_method = 'GCash';
$payment_reference = null;
$tx_status = 'pending';
$stmt3->bind_param('isdsss', $booking_id, $tx_type, $total, $payment_method, $payment_reference, $tx_status);
$stmt3->execute();

// redirect to checkout page to complete payment (simulated)
header('Location: /board-in/backend/checkout.php?booking_id=' . $booking_id);
exit;
exit;
