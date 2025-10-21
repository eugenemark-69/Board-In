<?php
/*
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

$booking_id = intval($_GET['booking_id'] ?? 0);
if ($booking_id <= 0) {
    flash('error', 'Invalid booking');
    header('Location: /board-in/pages/search.php');
    exit;
}

$stmt = $conn->prepare('SELECT b.*, bh.title FROM bookings b LEFT JOIN boarding_houses bh ON bh.id = b.boarding_house_id WHERE b.id = ? LIMIT 1');
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$res = $stmt->get_result();
$b = $res->fetch_assoc();
if (!$b) {
    flash('error', 'Booking not found');
    header('Location: /board-in/pages/search.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<h2>Checkout</h2>
<p>Booking reference: <strong><?php echo esc_attr($b['booking_reference']); ?></strong></p>
<p>Listing: <?php echo esc_attr($b['title']); ?></p>
<p>Total: ₱<?php echo number_format($b['total_amount'],2); ?></p>

<form method="post" action="/board-in/backend/simulate-payment.php">
  <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
  <div class="mb-3"><label class="form-label">GCash transaction ID (simulate)</label><input name="txid" class="form-control" required></div>
  <button class="btn btn-primary">Simulate Pay with GCash</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
*/