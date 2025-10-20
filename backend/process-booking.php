<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['student', 'admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Invalid request method');
    header('Location: /board-in/pages/search.php');
    exit;
}

$listing_id = intval($_POST['listing_id'] ?? 0);
$move_in_date = trim($_POST['move_in_date'] ?? '');
$agree_terms = isset($_POST['agree_terms']);
$user_id = $_SESSION['user']['id'] ?? 0;

if (!$listing_id || !$move_in_date || !$agree_terms) {
    flash('error', 'Please fill all required fields');
    header('Location: /board-in/pages/search.php');
    exit;
}

// get listing info
$stmt = $conn->prepare('SELECT id, title, monthly_rent, security_deposit, available_rooms, landlord_id FROM boarding_houses WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $listing_id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if (!$listing) {
    flash('error', 'Listing not found');
    header('Location: /board-in/pages/search.php');
    exit;
}

if ($listing['available_rooms'] <= 0) {
    flash('error', 'No rooms available for this listing');
    header('Location: /board-in/pages/search.php');
    exit;
}

// generate booking reference
$booking_ref = 'BOOK-' . strtoupper(bin2hex(random_bytes(4)));

$monthly = floatval($listing['monthly_rent']);
$deposit = floatval($listing['security_deposit']);
$total = $monthly + $deposit;
$commission = $monthly * PLATFORM_COMMISSION_RATE;
$payment_status = 'pending';
$booking_status = 'pending';
$landlord_id = $listing['landlord_id'];

// ✅ FIX: use correct column name (bh_id, user_id)
$stmt2 = $conn->prepare('
    INSERT INTO bookings 
    (booking_reference, user_id, bh_id, landlord_id, move_in_date, monthly_rent, security_deposit, total_amount, commission_amount, payment_status, booking_status, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');

$stmt2->bind_param(
    'siiisddddss',
    $booking_ref,
    $user_id,
    $listing_id,
    $landlord_id,
    $move_in_date,
    $monthly,
    $deposit,
    $total,
    $commission,
    $payment_status,
    $booking_status
);

if ($stmt2->execute()) {
    // reduce available room count
    $conn->query("UPDATE boarding_houses SET available_rooms = available_rooms - 1 WHERE id = {$listing_id}");

    flash('success', 'Booking successful! Proceed to payment.');
    header('Location: /board-in/backend/checkout.php?ref=' . urlencode($booking_ref));
    exit;
} else {
    flash('error', 'Booking failed: ' . $conn->error);
    header('Location: /board-in/pages/search.php');
    exit;
}
?>
