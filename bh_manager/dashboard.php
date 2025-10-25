<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['landlord','admin']);

$user_id = $_SESSION['user']['id'];

// Handle booking acceptance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_booking'])) {
    $booking_id = intval($_POST['booking_id']);
    
    // Verify this booking belongs to the landlord
    $stmt = $conn->prepare('SELECT id FROM bookings WHERE id = ? AND landlord_id = ? AND booking_status = "pending"');
    $stmt->bind_param('ii', $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update booking status to confirmed and payment status to paid
        $stmt = $conn->prepare('UPDATE bookings SET booking_status = "confirmed", payment_status = "paid", updated_at = NOW() WHERE id = ?');
        $stmt->bind_param('i', $booking_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Booking accepted successfully!";
        } else {
            $_SESSION['error'] = "Failed to accept booking.";
        }
    } else {
        $_SESSION['error'] = "Invalid booking or already processed.";
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

require_once __DIR__ . '/../includes/header.php';

// Listings summary - FIXED: Changed manager_id to user_id
$stmt = $conn->prepare('SELECT COUNT(*) AS total, SUM(CASE WHEN status = "available" OR status = "active" THEN 1 ELSE 0 END) AS available FROM boarding_houses WHERE user_id = ?');
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

// Recent bookings list - FIXED: Changed boarding_house_id to bh_id
$stmt = $conn->prepare('SELECT b.id, b.booking_reference, b.total_amount, b.commission_amount, b.payment_status, b.booking_status, b.created_at, u.full_name AS student_name, bh.title AS listing_title FROM bookings b LEFT JOIN users u ON u.id = b.student_id LEFT JOIN boarding_houses bh ON bh.id = b.bh_id WHERE b.landlord_id = ? ORDER BY b.created_at DESC LIMIT 10');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$recentBookings = $stmt->get_result();

// Get landlord's listings for verification display - NEW
$stmt = $conn->prepare('SELECT id, title, status, verification_status, verification_rejection_count FROM boarding_houses WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$listings = $stmt->get_result();

?>

<div class="container mt-4">
  <h2><i class="bi bi-speedometer2"></i> Landlord Dashboard</h2>
  
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <!-- Stats Cards -->
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

  <!-- My Listings with Verification Status -->
  <h4 class="mt-4"><i class="bi bi-houses"></i> My Listings</h4>
  <div class="table-responsive">
    <table class="table table-striped table-hover">
      <thead class="table-light">
        <tr>
          <th>Title</th>
          <th>Status</th>
          <th>Verification</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($listings->num_rows === 0): ?>
          <tr>
            <td colspan="4" class="text-center text-muted">
              No listings yet. <a href="/board-in/bh_manager/add-listing.php">Add your first listing</a>
            </td>
          </tr>
        <?php else: ?>
          <?php while ($listing = $listings->fetch_assoc()): ?>
            <tr>
              <td>
                <strong><?php echo htmlspecialchars($listing['title']); ?></strong>
              </td>
              <td>
                <span class="badge bg-<?php echo $listing['status'] === 'active' ? 'success' : ($listing['status'] === 'pending' ? 'warning' : 'secondary'); ?>">
                  <?php echo ucfirst($listing['status']); ?>
                </span>
              </td>
              <td>
                <?php if ($listing['verification_status'] === 'verified'): ?>
                  <span class="badge bg-success">
                    <i class="bi bi-patch-check-fill"></i> Verified
                  </span>
                <?php elseif ($listing['verification_status'] === 'pending_verification'): ?>
                  <span class="badge bg-warning">
                    <i class="bi bi-clock"></i> Pending
                  </span>
                <?php elseif ($listing['verification_status'] === 'rejected'): ?>
                  <span class="badge bg-danger">
                    <i class="bi bi-x-circle"></i> Rejected (<?php echo $listing['verification_rejection_count']; ?>/3)
                  </span>
                <?php else: ?>
                  <span class="badge bg-secondary">
                    <i class="bi bi-shield-x"></i> Unverified
                  </span>
                <?php endif; ?>
              </td>
              <td>
                <div class="btn-group btn-group-sm" role="group">
                  <a href="/board-in/pages/listing.php?id=<?php echo $listing['id']; ?>" 
                     class="btn btn-outline-info" title="View">
                    <i class="bi bi-eye"></i>
                  </a>
                  
                  <?php if ($listing['verification_status'] === 'unverified' && in_array($listing['status'], ['active', 'available'])): ?>
                    <a href="/board-in/bh_manager/request-verification.php?id=<?php echo $listing['id']; ?>" 
                       class="btn btn-outline-primary" title="Get Verified">
                      <i class="bi bi-patch-check"></i> Get Verified
                    </a>
                  <?php elseif ($listing['verification_status'] === 'pending_verification'): ?>
                    <a href="/board-in/bh_manager/verification-status.php?id=<?php echo $listing['id']; ?>" 
                       class="btn btn-outline-warning" title="Track Verification">
                      <i class="bi bi-clock-history"></i> Track
                    </a>
                  <?php elseif ($listing['verification_status'] === 'rejected' && $listing['verification_rejection_count'] < 3): ?>
                    <a href="/board-in/bh_manager/request-verification.php?id=<?php echo $listing['id']; ?>" 
                       class="btn btn-outline-danger" title="Resubmit">
                      <i class="bi bi-arrow-clockwise"></i> Resubmit
                    </a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Recent Bookings -->
  <h4 class="mt-4"><i class="bi bi-calendar-check"></i> Recent Bookings</h4>
  <div class="table-responsive">
    <table class="table table-striped">
      <thead class="table-light">
        <tr>
          <th>Ref</th>
          <th>Listing</th>
          <th>Student</th>
          <th>Amount</th>
          <th>Commission</th>
          <th>Payment</th>
          <th>Status</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($recentBookings->num_rows === 0): ?>
          <tr>
            <td colspan="9" class="text-center text-muted">No bookings yet</td>
          </tr>
        <?php else: ?>
          <?php while ($b = $recentBookings->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($b['booking_reference'] ?? 'N/A'); ?></td>
              <td><?php echo htmlspecialchars($b['listing_title'] ?? 'N/A'); ?></td>
              <td><?php echo htmlspecialchars($b['student_name'] ?? 'N/A'); ?></td>
              <td>₱<?php echo number_format(floatval($b['total_amount'] ?? 0), 2); ?></td>
              <td>₱<?php echo number_format(floatval($b['commission_amount'] ?? 0), 2); ?></td>
              <td>
                <span class="badge bg-<?php echo $b['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                  <?php echo ucfirst($b['payment_status'] ?? 'unpaid'); ?>
                </span>
              </td>
              <td>
                <span class="badge bg-<?php echo $b['booking_status'] === 'confirmed' ? 'success' : 'secondary'; ?>">
                  <?php echo ucfirst($b['booking_status'] ?? 'pending'); ?>
                </span>
              </td>
              <td><?php echo date('M d, Y', strtotime($b['created_at'])); ?></td>
              <td>
                <?php if ($b['booking_status'] === 'pending'): ?>
                  <form method="POST" style="display: inline;">
                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                    <button type="submit" name="accept_booking" class="btn btn-sm btn-success" 
                            onclick="return confirm('Accept this booking and mark as paid?')">
                      <i class="bi bi-check-circle"></i> Accept
                    </button>
                  </form>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Action Buttons -->
  <div class="mt-4 mb-5">
    <a href="/board-in/bh_manager/my-listings.php" class="btn btn-primary">
      <i class="bi bi-list-ul"></i> Manage All Listings
    </a>
    <a href="/board-in/bh_manager/manage-bookings.php" class="btn btn-outline-secondary">
      <i class="bi bi-calendar3"></i> Manage Bookings
    </a>
    <a href="/board-in/bh_manager/add-listing.php" class="btn btn-success">
      <i class="bi bi-plus-circle"></i> Add New Listing
    </a>
  </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>