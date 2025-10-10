<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['landlord','admin']);
require_once __DIR__ . '/../includes/header.php';

$user_id = $_SESSION['user']['id'];

// Listings summary
$stmt = $conn->prepare('SELECT COUNT(*) AS total, SUM(CASE WHEN status = "available" THEN 1 ELSE 0 END) AS available FROM boarding_houses WHERE manager_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$listingStats = $res->fetch_assoc();

// Bookings summary
$stmt = $conn->prepare('SELECT COUNT(*) AS total, SUM(CASE WHEN payment_status = "paid" THEN 1 ELSE 0 END) AS paid, SUM(CASE WHEN booking_status = "pending" THEN 1 ELSE 0 END) AS pending FROM bookings WHERE landlord_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$bookingStats = $res->fetch_assoc();

// Earnings (gross and net to landlord) from paid bookings
$stmt = $conn->prepare('SELECT COALESCE(SUM(total_amount),0) AS gross, COALESCE(SUM(total_amount - commission_amount),0) AS net FROM bookings WHERE landlord_id = ? AND payment_status = "paid"');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$earnings = $res->fetch_assoc();

// Commission owed from landlords table (if exists)
$commission_owed = 0.00;
if ($conn->query("SHOW TABLES LIKE 'landlords'")->num_rows > 0) {
    $stmt = $conn->prepare('SELECT commission_owed FROM landlords WHERE user_id = ? LIMIT 1');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if ($row) $commission_owed = floatval($row['commission_owed']);
}

// Recent bookings list
$stmt = $conn->prepare('SELECT b.id, b.booking_reference, b.total_amount, b.commission_amount, b.payment_status, b.booking_status, b.created_at, u.full_name AS student_name, bh.title AS listing_title FROM bookings b LEFT JOIN users u ON u.id = b.student_id LEFT JOIN boarding_houses bh ON bh.id = b.boarding_house_id WHERE b.landlord_id = ? ORDER BY b.created_at DESC LIMIT 10');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$recentBookings = $stmt->get_result();

?>

<div class="container mt-4">
  <h2>Landlord Dashboard</h2>
  <div class="row my-3">
    <div class="col-md-3">
      <div class="card text-white bg-primary mb-3">
        <div class="card-body">
          <h5 class="card-title">Listings</h5>
          <p class="card-text display-6"><?php echo intval($listingStats['total'] ?? 0); ?></p>
          <small><?php echo intval($listingStats['available'] ?? 0); ?> available</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-white bg-success mb-3">
        <div class="card-body">
          <h5 class="card-title">Bookings</h5>
          <p class="card-text display-6"><?php echo intval($bookingStats['total'] ?? 0); ?></p>
          <small><?php echo intval($bookingStats['paid'] ?? 0); ?> paid · <?php echo intval($bookingStats['pending'] ?? 0); ?> pending</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-dark bg-light mb-3">
        <div class="card-body">
          <h5 class="card-title">Gross Earnings</h5>
          <p class="card-text display-6">₱<?php echo number_format(floatval($earnings['gross'] ?? 0), 2); ?></p>
          <small>Total from paid bookings</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-dark bg-warning mb-3">
        <div class="card-body">
          <h5 class="card-title">Commission Owed</h5>
          <p class="card-text display-6">₱<?php echo number_format($commission_owed, 2); ?></p>
          <small>Platform commission pending payout</small>
        </div>
      </div>
    </div>
  </div>

  <h4 class="mt-4">Recent Bookings</h4>
  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Ref</th>
          <th>Listing</th>
          <th>Student</th>
          <th>Amount</th>
          <th>Commission</th>
          <th>Payment</th>
          <th>Status</th>
          <th>Created</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($b = $recentBookings->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($b['booking_reference'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($b['listing_title'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($b['student_name'] ?? ''); ?></td>
            <td>₱<?php echo number_format(floatval($b['total_amount'] ?? 0), 2); ?></td>
            <td>₱<?php echo number_format(floatval($b['commission_amount'] ?? 0), 2); ?></td>
            <td><?php echo htmlspecialchars($b['payment_status'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($b['booking_status'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($b['created_at'] ?? ''); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    <a href="/board-in/bh_manager/my-listings.php" class="btn btn-outline-primary">Manage Listings</a>
    <a href="/board-in/bh_manager/manage-bookings.php" class="btn btn-outline-secondary">Manage Bookings</a>
  </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
