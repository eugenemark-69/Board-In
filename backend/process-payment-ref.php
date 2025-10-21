<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';

// ✅ Establish the database connection
$conn = getDB();

// ✅ Make sure user is logged in
if (empty($_SESSION['user']['id'])) {
    die("Access denied. Please log in first.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = intval($_POST['booking_id'] ?? 0);
    $payment_reference = trim($_POST['payment_reference'] ?? '');

    if ($booking_id <= 0 || empty($payment_reference)) {
        die("Invalid request. Missing data.");
    }

    // ✅ Update payment info in database
    $stmt = $conn->prepare("
        UPDATE bookings 
        SET payment_reference = ?, payment_status = 'paid', updated_at = NOW()
        WHERE id = ? AND student_id = ?
    ");
    $stmt->execute([$payment_reference, $booking_id, $_SESSION['user']['id']]);

    // ✅ Redirect on success
    if ($stmt->rowCount() > 0) {
        header("Location: /board-in/student/my-bookings.php?success=1");
        exit;
    } else {
        echo "<p>❌ Update failed — booking not found or not yours.</p>";
    }
} else {
    echo "<p>Invalid access.</p>";
}
