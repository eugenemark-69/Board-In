<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['student']);

$conn = getDB();
$student_id = $_SESSION['user']['id'] ?? 0;
$listing_id = $_POST['listing_id'] ?? 0;

if (!$student_id || !$listing_id) {
    header('Location: /board-in/pages/listings.php?error=missing_data');
    exit;
}

// ✅ Generate a booking reference
$booking_ref = 'BK-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

// ✅ Get listing details
$stmt = $conn->prepare("SELECT price AS monthly_rent FROM listings WHERE id = ?");
$stmt->execute([$listing_id]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$listing) {
    header('Location: /board-in/pages/listings.php?error=listing_not_found');
    exit;
}

$monthly_rent = $listing['monthly_rent'];
$total_amount = $monthly_rent;
$landlord_id = null; // or 0 if your column doesn’t allow NULL

// ✅ Insert into bookings table
$stmt = $conn->prepare("
    INSERT INTO bookings (listing_id, student_id, landlord_id, booking_reference, monthly_rent, total_amount, payment_status, booking_status)
    VALUES (?, ?, ?, ?, ?, ?, 'pending', 'pending')
");
$stmt->execute([$listing_id, $student_id, $landlord_id, $booking_ref, $monthly_rent, $total_amount]);

header('Location: /board-in/student/my-bookings.php?success=1');
exit;
?>
