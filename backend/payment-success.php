<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['student', 'admin']);

$booking_id = intval($_GET['booking_id'] ?? 12);
// var_dump($booking_id);
// exit;
if ($booking_id <= 0) {
    flash('error', 'Invalid booking ID');
    header('Location: /board-in/student/my-bookings.php');
    exit;
}

// Verify and retrieve payment from PayMongo
$source_id = $_GET['source_id'] ?? '';
$payment_intent_id = $_GET['payment_intent_id'] ?? '';

// Get booking
$stmt = $conn->prepare('
    SELECT b.*, bh.title, bh.address, u.full_name AS landlord_name, u.email AS landlord_email
    FROM bookings b
    LEFT JOIN boarding_houses bh ON bh.id = b.bh_id
    LEFT JOIN users u ON u.id = b.landlord_id
    WHERE b.id = ? AND b.user_id = ?
');
$stmt->bind_param('ii', $booking_id, $_SESSION['user']['id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    flash('error', 'Booking not found');
    header('Location: /board-in/student/my-bookings.php');
    exit;
}

// Verify payment status with PayMongo
$payment_verified = false;
$payment_id = '';

if ($source_id) {
    // Check e-wallet source
    $payment_data = verifyPayMongoSource($source_id);
    if ($payment_data && $payment_data['status'] === 'chargeable') {
        // Create charge
        $charge_result = createPayMongoCharge($source_id, $booking['total_amount'] * 100);
        if ($charge_result['success']) {
            $payment_verified = true;
            $payment_id = $charge_result['id'];
        }
    }
} elseif ($payment_intent_id) {
    // Check payment intent (card)
    $payment_data = verifyPaymentIntent($payment_intent_id);
    if ($payment_data && $payment_data['status'] === 'succeeded') {
        $payment_verified = true;
        $payment_id = $payment_intent_id;
    }
}

if ($payment_verified) {
    // Update booking status
    $stmt = $conn->prepare('UPDATE bookings SET payment_status = ?, payment_reference = ?, status = ? WHERE id = ?');
    $paid = 'paid';
    $confirmed = 'confirmed';
    $stmt->bind_param('sssi', $paid, $payment_id, $confirmed, $booking_id);
    $stmt->execute();
    
    // Update transaction
    $stmt = $conn->prepare('
        UPDATE transactions 
        SET status = ?, payment_reference = ?, processed_at = NOW() 
        WHERE booking_id = ? AND transaction_type = ?
    ');
    $completed = 'completed';
    $type = 'booking_payment';
    $stmt->bind_param('ssis', $completed, $payment_id, $booking_id, $type);
    $stmt->execute();
    
    // Calculate and record commission
    $commission = $booking['total_amount'] * PLATFORM_COMMISSION_RATE;
    $stmt = $conn->prepare('
        UPDATE landlords 
        SET commission_owed = commission_owed + ? 
        WHERE user_id = ?
    ');
    $stmt->bind_param('di', $commission, $booking['landlord_id']);
    $stmt->execute();
    
    // Send notifications
    sendPaymentNotifications($conn, $booking, $payment_id);
    
    // Log activity
    $stmt = $conn->prepare('
        INSERT INTO activity_logs (user_id, action, description, created_at) 
        VALUES (?, ?, ?, NOW())
    ');
    $action = 'payment_completed';
    $description = 'Payment completed for booking ' . $booking['booking_reference'] . ' - Payment ID: ' . $payment_id;
    $stmt->bind_param('iss', $_SESSION['user']['id'], $action, $description);
    $stmt->execute();
    
    $_SESSION['payment_success'] = true;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if ($payment_verified): ?>
                <!-- Success Message -->
                <div class="card shadow-lg border-0">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="text-success mb-3">Payment Successful!</h2>
                        <p class="lead text-muted mb-4">Your booking has been confirmed</p>
                        
                        <div class="alert alert-success">
                            <strong>Payment ID:</strong> <?php echo esc_attr($payment_id); ?><br>
                            <strong>Booking Reference:</strong> <?php echo esc_attr($booking['booking_reference']); ?><br>
                            <strong>Amount Paid:</strong> ₱<?php echo number_format($booking['total_amount'], 2); ?>
                        </div>
                        
                        <div class="mb-4">
                            <h5>What's Next?</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2">✓ Confirmation email sent to your inbox</li>
                                <li class="mb-2">✓ Landlord will be notified</li>
                                <li class="mb-2">✓ You can now view your booking details</li>
                            </ul>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="/board-in/student/booking-confirmation.php?id=<?php echo $booking_id; ?>" 
                               class="btn btn-primary btn-lg">
                                <i class="bi bi-file-text me-2"></i>View Booking Details
                            </a>
                            <a href="/board-in/student/my-bookings.php" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-list-ul me-2"></i>My Bookings
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Payment Pending/Verification -->
                <div class="card shadow-lg border-0">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-hourglass-split text-warning" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="text-warning mb-3">Payment Verification Pending</h2>
                        <p class="lead text-muted mb-4">We're verifying your payment. This usually takes a few moments.</p>
                        
                        <div class="alert alert-warning">
                            <strong>Booking Reference:</strong> <?php echo esc_attr($booking['booking_reference']); ?><br>
                            <strong>Status:</strong> Pending Verification
                        </div>
                        
                        <p>You'll receive an email confirmation once payment is verified.</p>
                        
                        <a href="/board-in/pages/index.php" class="btn btn-primary btn-lg mt-3">
                            <i class="bi bi-arrow-left me-2"></i>Go Back Home
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';

// ============================================
// HELPER FUNCTIONS
// ============================================

function verifyPayMongoSource($source_id) {
    $ch = curl_init('https://api.paymongo.com/v1/sources/' . $source_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':')
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        return $data['data']['attributes'] ?? null;
    }
    return null;
}

function verifyPaymentIntent($intent_id) {
    $ch = curl_init('https://api.paymongo.com/v1/payment_intents/' . $intent_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':')
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        return $data['data']['attributes'] ?? null;
    }
    return null;
}

function createPayMongoCharge($source_id, $amount) {
    $data = [
        'data' => [
            'attributes' => [
                'amount' => $amount,
                'source' => [
                    'id' => $source_id,
                    'type' => 'source'
                ],
                'currency' => 'PHP',
                'description' => 'Board-In Booking Payment'
            ]
        ]
    ];
    
    $ch = curl_init('https://api.paymongo.com/v1/charges');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':')
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $result = json_decode($response, true);
        if (isset($result['data']['id'])) {
            return [
                'success' => true,
                'id' => $result['data']['id'],
                'status' => $result['data']['attributes']['status']
            ];
        }
    }
    return ['success' => false];
}

function sendPaymentNotifications($conn, $booking, $payment_id) {
    // Notify student
    $stmt = $conn->prepare('
        INSERT INTO notifications (user_id, title, message, type, link, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ');
    $title = 'Payment Successful';
    $message = 'Your payment of ₱' . number_format($booking['total_amount'], 2) . ' for booking ' . $booking['booking_reference'] . ' has been confirmed.';
    $type = 'success';
    $link = '/board-in/student/booking-confirmation.php?id=' . $booking['id'];
    $stmt->bind_param('issss', $booking['user_id'], $title, $message, $type, $link);
    $stmt->execute();
    
    // Notify landlord
    if ($booking['landlord_id']) {
        $title = 'New Booking Payment Received';
        $message = 'Payment of ₱' . number_format($booking['total_amount'], 2) . ' received for booking ' . $booking['booking_reference'];
        $link = '/board-in/landlord/bookings.php';
        $stmt->bind_param('issss', $booking['landlord_id'], $title, $message, $type, $link);
        $stmt->execute();
    }
}
?>