<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['student','admin']);

$id = intval($_GET['id'] ?? 0);

require_once __DIR__ . '/../includes/header.php';

if ($id <= 0) {
    echo '<div class="container mt-4">
            <div class="alert alert-warning text-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Invalid listing ID. Please select a boarding house to book.
            </div>
            <div class="text-center mt-3">
                <a href="/board-in/pages/listings.php" class="btn btn-primary">Browse Listings</a>
            </div>
          </div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$stmt = $conn->prepare('SELECT id, title, monthly_rent, security_deposit, available_rooms, status FROM boarding_houses WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$listing = $res->fetch_assoc();

if (!$listing) {
    echo '<div class="container mt-4">
            <div class="alert alert-danger text-center" role="alert">
                <i class="bi bi-x-circle-fill me-2"></i>
                The listing you are trying to book was not found.
            </div>
            <div class="text-center mt-3">
                <a href="/board-in/pages/listings.php" class="btn btn-primary">Browse Listings</a>
            </div>
          </div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Check if listing is available
if ($listing['status'] !== 'active' && $listing['status'] !== 'available') {
    echo '<div class="container mt-4">
            <div class="alert alert-warning text-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                This listing is currently not available for booking.
            </div>
            <div class="text-center mt-3">
                <a href="/board-in/pages/listings.php" class="btn btn-primary">Browse Other Listings</a>
            </div>
          </div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Check if user already has a pending booking for this listing
$user_id = $_SESSION['user']['id'];
$check_stmt = $conn->prepare('SELECT id FROM bookings WHERE bh_id = ? AND user_id = ? AND booking_status IN ("pending", "confirmed") LIMIT 1');
$check_stmt->bind_param('ii', $id, $user_id);
$check_stmt->execute();
$existing_booking = $check_stmt->get_result()->fetch_assoc();

if ($existing_booking) {
    echo '<div class="container mt-4">
            <div class="alert alert-info text-center" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                You already have a pending or confirmed booking for this listing.
            </div>
            <div class="text-center mt-3">
                <a href="/board-in/pages/my-bookings.php" class="btn btn-primary">View My Bookings</a>
            </div>
          </div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-calendar-check"></i> Book: <?php echo htmlspecialchars($listing['title']); ?></h4>
                </div>
                <div class="card-body">
                    <form method="post" action="/board-in/backend/process-booking.php" id="bookingForm">
                        <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-calendar-event"></i> Move-in Date</label>
                            <input name="move_in_date" type="date" class="form-control" required 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            <small class="text-muted">Select your preferred move-in date</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-cash"></i> Monthly Rent</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input class="form-control" value="<?php echo number_format($listing['monthly_rent'], 2); ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-shield-check"></i> Security Deposit</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input class="form-control" value="<?php echo number_format($listing['security_deposit'], 2); ?>" disabled>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-4">
                            <h5>Total Amount to Pay</h5>
                            <p class="display-6 text-primary">
                                <strong>₱<?php echo number_format($listing['monthly_rent'] + $listing['security_deposit'], 2); ?></strong>
                            </p>
                            <small class="text-muted">First month rent + Security deposit</small>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="agree_terms" id="agree_terms" required>
                            <label class="form-check-label" for="agree_terms">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">terms and conditions</a>
                            </label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-credit-card"></i> Proceed to Payment (GCash)
                            </button>
                            <a href="/board-in/pages/listing.php?id=<?php echo $listing['id']; ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Listing
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Booking Terms:</h6>
                <ul>
                    <li>Payment must be completed to confirm your booking</li>
                    <li>Security deposit is refundable upon checkout</li>
                    <li>Cancellation policy applies as per landlord's terms</li>
                    <li>You must comply with house rules</li>
                    <li>Contact landlord for any special requests</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>