<?php
require_once __DIR__ . '/../config/session.php';
require_role(['landlord', 'admin']);
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /board-in/bh_manager/manage-bookings.php');
    exit;
}

$booking_id = intval($_POST['booking_id'] ?? 0);
$landlord_id = $_SESSION['user']['id'];

if ($booking_id <= 0) {
    $_SESSION['error'] = 'Invalid booking ID';
    header('Location: /board-in/bh_manager/manage-bookings.php');
    exit;
}

// Verify this booking belongs to the landlord
$stmt = $conn->prepare('SELECT b.*, bh.user_id FROM bookings b JOIN boarding_houses bh ON bh.id = b.bh_id WHERE b.id = ?');
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    $_SESSION['error'] = 'Booking not found';
    header('Location: /board-in/bh_manager/manage-bookings.php');
    exit;
}

if ($booking['user_id'] != $landlord_id) {
    $_SESSION['error'] = 'Unauthorized access';
    header('Location: /board-in/bh_manager/manage-bookings.php');
    exit;
}

// Update payment status to 'paid'
$stmt = $conn->prepare('UPDATE bookings SET payment_status = "paid", updated_at = NOW() WHERE id = ?');
$stmt->bind_param('i', $booking_id);

if ($stmt->execute()) {
    // Create a transaction record
    $stmt = $conn->prepare('INSERT INTO transactions (booking_id, transaction_type, amount, payment_method, status, processed_at) VALUES (?, "booking_payment", ?, "Manual Confirmation", "completed", NOW())');
    $stmt->bind_param('id', $booking_id, $booking['total_amount']);
    $stmt->execute();
    
    // Send notification to student
    $stmt = $conn->prepare('INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, "Payment Confirmed", "Your payment has been confirmed by the landlord. Waiting for move-in confirmation.", "success", "/board-in/student/my-bookings.php")');
    $stmt->bind_param('i', $booking['student_id']);
    $stmt->execute();
    
    $_SESSION['success'] = 'Payment confirmed successfully! Now you can confirm when the student moves in.';
} else {
    $_SESSION['error'] = 'Failed to confirm payment: ' . $conn->error;
}

header('Location: /board-in/bh_manager/manage-bookings.php');
exit;