<?php
require_once __DIR__ . '/../config/config.php';

// Get raw POST data
$raw_input = file_get_contents('php://input');
$payload = json_decode($raw_input, true);

// Log webhook for debugging
error_log('PayMongo Webhook Received: ' . $raw_input);

// Verify webhook signature (IMPORTANT for security)
$signature = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? '';
if (!verifyWebhookSignature($raw_input, $signature, PAYMONGO_WEBHOOK_SECRET)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

// Process webhook event
if (!isset($payload['data']['attributes']['type'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

$event_type = $payload['data']['attributes']['type'];
$event_data = $payload['data']['attributes']['data'];

switch ($event_type) {
    case 'source.chargeable':
        handleSourceChargeable($conn, $event_data);
        break;
        
    case 'payment.paid':
        handlePaymentPaid($conn, $event_data);
        break;
        
    case 'payment.failed':
        handlePaymentFailed($conn, $event_data);
        break;
        
    default:
        error_log('Unhandled webhook event: ' . $event_type);
}

http_response_code(200);
echo json_encode(['success' => true]);
exit;

// ============================================
// WEBHOOK HANDLERS
// ============================================

function handleSourceChargeable($conn, $data) {
    $source_id = $data['id'] ?? '';
    
    if (empty($source_id)) {
        error_log('Webhook: Missing source ID');
        return;
    }
    
    // Find booking with this source
    $stmt = $conn->prepare('SELECT id, total_amount FROM bookings WHERE payment_reference = ? LIMIT 1');
    $stmt->bind_param('s', $source_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    
    if (!$booking) {
        error_log('Webhook: Booking not found for source ' . $source_id);
        return;
    }
    
    // Create charge for this source
    $amount = intval($booking['total_amount'] * 100);
    $charge_result = createCharge($source_id, $amount);
    
    if ($charge_result['success']) {
        error_log('Webhook: Charge created successfully for booking ' . $booking['id']);
    } else {
        error_log('Webhook: Failed to create charge - ' . $charge_result['error']);
    }
}

function handlePaymentPaid($conn, $data) {
    $payment_id = $data['id'] ?? '';
    $source_id = $data['attributes']['source']['id'] ?? '';
    
    // Find booking
    $stmt = $conn->prepare('SELECT id, user_id, landlord_id, total_amount, booking_reference FROM bookings WHERE payment_reference = ? LIMIT 1');
    $stmt->bind_param('s', $source_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    
    if (!$booking) {
        // Try finding by payment_id
        $stmt = $conn->prepare('SELECT id, user_id, landlord_id, total_amount, booking_reference FROM bookings WHERE payment_reference = ? LIMIT 1');
        $stmt->bind_param('s', $payment_id);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        
        if (!$booking) {
            error_log('Webhook: Booking not found for payment ' . $payment_id);
            return;
        }
    }
    
    // Update booking
    $stmt = $conn->prepare('UPDATE bookings SET payment_status = ?, payment_reference = ?, status = ? WHERE id = ?');
    $paid = 'paid';
    $confirmed = 'confirmed';
    $stmt->bind_param('sssi', $paid, $payment_id, $confirmed, $booking['id']);
    $stmt->execute();
    
    // Update transaction
    $stmt = $conn->prepare('
        UPDATE transactions 
        SET status = ?, payment_reference = ?, processed_at = NOW() 
        WHERE booking_id = ? AND transaction_type = ?
    ');
    $completed = 'completed';
    $type = 'booking_payment';
    $stmt->bind_param('ssis', $completed, $payment_id, $booking['id'], $type);
    $stmt->execute();
    
    // Calculate commission
    $commission = $booking['total_amount'] * PLATFORM_COMMISSION_RATE;
    $stmt = $conn->prepare('UPDATE landlords SET commission_owed = commission_owed + ? WHERE user_id = ?');
    $stmt->bind_param('di', $commission, $booking['landlord_id']);
    $stmt->execute();
    
    // Send notifications
    sendPaymentNotifications($conn, $booking);
    
    error_log('Webhook: Payment completed for booking ' . $booking['id']);
}

function handlePaymentFailed($conn, $data) {
    $payment_id = $data['id'] ?? '';
    $source_id = $data['attributes']['source']['id'] ?? '';
    
    // Find booking
    $stmt = $conn->prepare('SELECT id FROM bookings WHERE payment_reference IN (?, ?) LIMIT 1');
    $stmt->bind_param('ss', $source_id, $payment_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    
    if (!$booking) {
        error_log('Webhook: Booking not found for failed payment ' . $payment_id);
        return;
    }
    
    // Update booking
    $stmt = $conn->prepare('UPDATE bookings SET payment_status = ? WHERE id = ?');
    $failed = 'unpaid';
    $stmt->bind_param('si', $failed, $booking['id']);
    $stmt->execute();
    
    // Update transaction
    $stmt = $conn->prepare('
        UPDATE transactions 
        SET status = ? 
        WHERE booking_id = ? AND transaction_type = ?
    ');
    $failed_status = 'failed';
    $type = 'booking_payment';
    $stmt->bind_param('sis', $failed_status, $booking['id'], $type);
    $stmt->execute();
    
    error_log('Webhook: Payment failed for booking ' . $booking['id']);
}

// ============================================
// HELPER FUNCTIONS
// ============================================

function verifyWebhookSignature($payload, $signature, $secret) {
    if (empty($signature) || empty($secret)) {
        return false;
    }
    
    // PayMongo uses HMAC SHA256
    $computed_signature = hash_hmac('sha256', $payload, $secret);
    
    return hash_equals($computed_signature, $signature);
}

function createCharge($source_id, $amount) {
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
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 || $http_code === 201) {
        $result = json_decode($response, true);
        if (isset($result['data']['id'])) {
            return ['success' => true, 'id' => $result['data']['id']];
        }
    }
    
    return ['success' => false, 'error' => 'Failed to create charge'];
}

function sendPaymentNotifications($conn, $booking) {
    // Notify student
    $stmt = $conn->prepare('
        INSERT INTO notifications (user_id, title, message, type, link, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ');
    $title = 'Payment Confirmed';
    $message = 'Your payment for booking ' . $booking['booking_reference'] . ' has been confirmed.';
    $type = 'success';
    $link = '/board-in/student/booking-confirmation.php?id=' . $booking['id'];
    $stmt->bind_param('issss', $booking['user_id'], $title, $message, $type, $link);
    $stmt->execute();
    
    // Notify landlord
    if ($booking['landlord_id']) {
        $title = 'Payment Received';
        $message = 'Payment received for booking ' . $booking['booking_reference'];
        $link = '/board-in/landlord/bookings.php';
        $stmt->bind_param('issss', $booking['landlord_id'], $title, $message, $type, $link);
        $stmt->execute();
    }
}
?>