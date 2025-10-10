<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
  flash('error', 'Booking not found');
  header('Location: /board-in/pages/index.php');
  exit;
}

$stmt = $conn->prepare('SELECT b.*, bh.title, u.full_name AS landlord_name FROM bookings b LEFT JOIN boarding_houses bh ON bh.id = b.boarding_house_id LEFT JOIN users u ON u.id = b.landlord_id WHERE b.id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$b = $res->fetch_assoc();
if (!$b) {
  flash('error', 'Booking not found');
  header('Location: /board-in/pages/index.php');
  exit;
}

?>

<h2>Booking Confirmation</h2>
<p>Reference: <strong><?php echo esc_attr($b['booking_reference']); ?></strong></p>
<p>Listing: <?php echo esc_attr($b['title']); ?></p>
<p>Amount paid: ₱<?php echo number_format($b['total_amount'],2); ?> | Payment ref: <?php echo esc_attr($b['payment_reference']); ?></p>
<p>Landlord: <?php echo esc_attr($b['landlord_name']); ?></p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
