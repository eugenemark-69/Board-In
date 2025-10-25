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

// Verify this booking belongs to the landlord and payment is confirmed
$stmt = $conn->prepare('SELECT b.*, bh.user_id, bh.id as bh_id FROM bookings b JOIN boarding_houses bh ON bh.id = b.bh_id WHERE b.id = ?');
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

if ($booking['payment_status'] !== 'paid') {
    $_SESSION['error'] = 'Payment must be confirmed before confirming move-in';
    header('Location: /board-in/bh_manager/manage-bookings.php');
    exit;
}

// Update booking status to 'confirmed' or 'active' and set landlord confirmation timestamp
$stmt = $conn->prepare('UPDATE bookings SET booking_status = "active", landlord_confirmed_at = NOW(), updated_at = NOW() WHERE id = ?');
$stmt->bind_param('i', $booking_id);

if ($stmt->execute()) {
    // Decrease available rooms in the boarding house
    $stmt = $conn->prepare('UPDATE boarding_houses SET available_rooms = available_rooms - 1 WHERE id = ? AND available_rooms > 0');
    $stmt->bind_param('i', $booking['bh_id']);
    $stmt->execute();
    
    // Send notification to student
    $stmt = $conn->prepare('INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, "Move-In Confirmed!", "Your move-in has been confirmed by the landlord. Welcome to your new home!", "success", "/board-in/student/my-bookings.php")');
    $stmt->bind_param('i', $booking['student_id']);
    $stmt->execute();
    
    $_SESSION['success'] = 'Move-in confirmed successfully! The booking is now active.';
} else {
    $_SESSION['error'] = 'Failed to confirm move-in: ' . $conn->error;
}

header('Location: /board-in/bh_manager/manage-bookings.php');
exit;