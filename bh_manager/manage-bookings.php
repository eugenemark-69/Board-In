<?php
require_once __DIR__ . '/../config/session.php';
require_role(['landlord','admin']);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

$landlord_id = $_SESSION['user']['id'];
$stmt = $conn->prepare('SELECT b.*, bh.title, u.full_name AS student_name FROM bookings b JOIN boarding_houses bh ON bh.id = b.boarding_house_id JOIN users u ON u.id = b.student_id WHERE bh.manager_id = ? ORDER BY b.created_at DESC');
$stmt->bind_param('i', $landlord_id);
$stmt->execute();
$res = $stmt->get_result();

?>

<h2>Manage Bookings</h2>
<?php while ($b = $res->fetch_assoc()): ?>
  <div class="card mb-2">
    <div class="card-body">
      <h5><?php echo esc_attr($b['booking_reference']); ?> — <?php echo esc_attr($b['title']); ?></h5>
      <p>Student: <?php echo esc_attr($b['student_name']); ?> | Move-in: <?php echo esc_attr($b['move_in_date']); ?></p>
      <p>Payment status: <?php echo esc_attr($b['payment_status']); ?> | Booking status: <?php echo esc_attr($b['booking_status']); ?></p>
      <?php if ($b['booking_status'] === 'pending' && $b['payment_status'] === 'paid'): ?>
        <form method="post" action="/board-in/backend/process-confirm-movein.php">
          <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
          <button class="btn btn-success">Confirm Move-In</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
<?php endwhile; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
