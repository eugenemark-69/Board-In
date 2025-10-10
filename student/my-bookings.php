<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['student','admin']);
require_once __DIR__ . '/../includes/header.php';

$student_id = $_SESSION['user']['id'];
$stmt = $conn->prepare('SELECT b.*, bh.title FROM bookings b LEFT JOIN boarding_houses bh ON bh.id = b.boarding_house_id WHERE b.student_id = ? ORDER BY b.created_at DESC');
$stmt->bind_param('i', $student_id);
$stmt->execute();
$res = $stmt->get_result();

?>

<h2>My Bookings</h2>
<?php while ($b = $res->fetch_assoc()): ?>
	<div class="card mb-2">
		<div class="card-body">
			<h5><?php echo esc_attr($b['booking_reference']); ?> — <?php echo esc_attr($b['title']); ?></h5>
			<p>Move-in: <?php echo esc_attr($b['move_in_date']); ?> | Total: ₱<?php echo number_format($b['total_amount'],2); ?></p>
			<p>Payment status: <?php echo esc_attr($b['payment_status']); ?> | Booking status: <?php echo esc_attr($b['booking_status']); ?></p>
			<?php if ($b['payment_status'] === 'pending'): ?>
				<form method="post" action="/board-in/backend/process-payment-ref.php" class="d-flex gap-2">
					<input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
					<input name="payment_reference" class="form-control form-control-sm" placeholder="GCash transaction ID">
					<button class="btn btn-sm btn-primary">Submit payment reference</button>
				</form>
			<?php else: ?>
				<p>Payment reference: <?php echo esc_attr($b['payment_reference']); ?></p>
			<?php endif; ?>
		</div>
	</div>
<?php endwhile; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
