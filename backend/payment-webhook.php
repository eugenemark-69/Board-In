<?php
/*
// Generic payment webhook receiver (expects JSON POST)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// Simple shared-secret check (set PAYMENT_PROVIDER_SECRET in config)
$secret = $_SERVER['HTTP_X_PROVIDER_SECRET'] ?? '';
if (PAYMENT_PROVIDER_SECRET && $secret !== PAYMENT_PROVIDER_SECRET) {
    http_response_code(403);
    echo json_encode(['error' => 'invalid_secret']);
    exit;
}

// Expected payload: { "booking_reference": "BK-2025-0001", "status": "completed", "amount": 3000.00, "transaction_id": "GC123" }
if (empty($data['booking_reference']) || empty($data['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_payload']);
    exit;
}

$ref = $conn->real_escape_string($data['booking_reference']);
$status = $data['status'];
$txid = $data['transaction_id'] ?? null;
$amount = isset($data['amount']) ? floatval($data['amount']) : null;

// find booking
$stmt = $conn->prepare('SELECT id FROM bookings WHERE booking_reference = ? LIMIT 1');
$stmt->bind_param('s', $ref);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'booking_not_found']);
    exit;
}
$b = $res->fetch_assoc();
$booking_id = $b['id'];

if ($status === 'completed' || $status === 'paid') {
    // update booking and transaction
    $stmt2 = $conn->prepare('UPDATE bookings SET payment_status = ?, payment_reference = ? WHERE id = ?');
    $paid = 'paid';
    $stmt2->bind_param('ssi', $paid, $txid, $booking_id);
    $stmt2->execute();

    // mark or create transaction
    $stmt3 = $conn->prepare('UPDATE transactions SET status = ?, payment_reference = ?, processed_at = NOW() WHERE booking_id = ? AND transaction_type = "booking_payment"');
    $done = 'completed';
    $stmt3->bind_param('ssi', $done, $txid, $booking_id);
    $stmt3->execute();

    // if no transaction updated, create one
    if ($conn->affected_rows === 0) {
        $stmt4 = $conn->prepare('INSERT INTO transactions (booking_id, transaction_type, amount, payment_method, payment_reference, status, processed_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $type = 'booking_payment';
        $method = 'GCash';
        $stmt4->bind_param('isdsss', $booking_id, $type, $amount, $method, $txid, $done);
        $stmt4->execute();
    }

    echo json_encode(['ok' => true]);
    exit;
}

// unsupported status
http_response_code(200);
echo json_encode(['ok' => true]);
exit;
*/
