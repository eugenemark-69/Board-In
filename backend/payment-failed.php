<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['student', 'admin']);
require_once __DIR__ . '/../includes/header.php';

$booking_id = intval($_GET['booking_id'] ?? 0);

if ($booking_id <= 0) {
    flash('error', 'Invalid booking ID');
    header('Location: /board-in/student/my-bookings.php');
    exit;
}

// Get booking
$stmt = $conn->prepare('SELECT * FROM bookings WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $booking_id, $_SESSION['user']['id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    flash('error', 'Booking not found');
    header('Location: /board-in/student/my-bookings.php');
    exit;
}

// Update payment status to failed
$stmt = $conn->prepare('UPDATE bookings SET payment_status = ? WHERE id = ?');
$failed = 'unpaid';
$stmt->bind_param('si', $failed, $booking_id);
$stmt->execute();

// Log activity
$stmt = $conn->prepare('
    INSERT INTO activity_logs (user_id, action, description, created_at) 
    VALUES (?, ?, ?, NOW())
');
$action = 'payment_failed';
$description = 'Payment failed for booking ' . $booking['booking_reference'];
$stmt->bind_param('iss', $_SESSION['user']['id'], $action, $description);
$stmt->execute();
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-x-circle-fill text-danger" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="text-danger mb-3">Payment Failed</h2>
                    <p class="lead text-muted mb-4">We couldn't process your payment</p>
                    
                    <div class="alert alert-danger">
                        <strong>Booking Reference:</strong> <?php echo esc_attr($booking['booking_reference']); ?><br>
                        <strong>Status:</strong> Payment Failed
                    </div>
                    
                    <h5 class="mb-3">Common Reasons:</h5>
                    <ul class="text-start list-unstyled mb-4">
                        <li class="mb-2">❌ Insufficient funds</li>
                        <li class="mb-2">❌ Payment was cancelled</li>
                        <li class="mb-2">❌ Network connection issue</li>
                        <li class="mb-2">❌ Invalid payment details</li>
                    </ul>
                    
                    <p class="mb-4">Don't worry! Your booking is still reserved. You can try again.</p>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="/board-in/backend/checkout.php?ref=<?php echo urlencode($booking['booking_reference']); ?>" 
                           class="btn btn-primary btn-lg">
                            <i class="bi bi-arrow-repeat me-2"></i>Try Again
                        </a>
                        <a href="/board-in/student/my-bookings.php" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-list-ul me-2"></i>My Bookings
                        </a>
                    </div>
                    
                    <div class="mt-4">
                        <small class="text-muted">
                            Need help? <a href="/board-in/pages/contact.php">Contact Support</a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>