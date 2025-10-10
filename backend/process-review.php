<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user'])) {
	flash('error', 'Please login');
	header('Location: /board-in/user/login.php');
	exit;
}

$student_id = $_SESSION['user']['id'];
$booking_id = intval($_POST['booking_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$clean = intval($_POST['cleanliness_rating'] ?? 0);
$loc = intval($_POST['location_rating'] ?? 0);
$val = intval($_POST['value_rating'] ?? 0);
$landlord_rating = intval($_POST['landlord_rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($booking_id <= 0 || $rating < 1 || $rating > 5) {
	flash('error', 'Invalid review submission');
	header('Location: /board-in/student/reviews.php');
	exit;
}

// load booking to get listing id
$stmt = $conn->prepare('SELECT boarding_house_id FROM bookings WHERE id = ? AND student_id = ? LIMIT 1');
$stmt->bind_param('ii', $booking_id, $student_id);
$stmt->execute();
$res = $stmt->get_result();
$b = $res->fetch_assoc();
if (!$b) {
	flash('error', 'Booking not found');
	header('Location: /board-in/student/reviews.php');
	exit;
}

$listing_id = $b['boarding_house_id'];

$stmt2 = $conn->prepare('INSERT INTO reviews (booking_id, student_id, boarding_house_id, rating, cleanliness_rating, location_rating, value_rating, landlord_rating, comment, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
$stmt2->bind_param('iiiiiiiis', $booking_id, $student_id, $listing_id, $rating, $clean, $loc, $val, $landlord_rating, $comment);
$stmt2->execute();

// optionally mark booking as completed/reviewed (not enforced here)

flash('success', 'Review submitted. Thank you!');
header('Location: /board-in/student/reviews.php');
exit;
