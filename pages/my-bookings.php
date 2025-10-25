<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['student','admin']);
require_once __DIR__ . '/../includes/header.php';

$user_id = $_SESSION['user']['id'];

// Get all bookings for this user
$stmt = $conn->prepare('
    SELECT 
        b.id, 
        b.booking_reference, 
        b.move_in_date, 
        b.check_in_date,
        b.total_amount, 
        b.commission_amount, 
        b.payment_status, 
        b.booking_status, 
        b.created_at,
        b.payment_reference,
        bh.title AS listing_title,
        bh.address,
        bh.city,
        u.full_name AS landlord_name,
        u.phone AS landlord_phone
    FROM bookings b
    LEFT JOIN boarding_houses bh ON bh.id = b.bh_id
    LEFT JOIN users u ON u.id = b.landlord_id
    WHERE b.user_id = ? OR b.student_id = ?
    ORDER BY b.created_at DESC
');
$stmt->bind_param('ii', $user_id, $user_id);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<div class="container mt-4">
    <h2><i class="bi bi-calendar-check"></i> My Bookings</h2>
    
    <?php if ($bookings->num_rows === 0): ?>
        <div class="alert alert-info text-center mt-4">
            <i class="bi bi-info-circle-fill me-2"></i>
            You haven't made any bookings yet.
            <div class="mt-3">
                <a href="/board-in/pages/listings.php" class="btn btn-primary">
                    <i class="bi bi-search"></i> Browse Listings
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row mt-4">
            <?php while ($booking = $bookings->fetch_assoc()): ?>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($booking['listing_title'] ?? 'N/A'); ?></h5>
                                <span class="badge bg-<?php echo $booking['booking_status'] === 'confirmed' ? 'success' : ($booking['booking_status'] === 'pending' ? 'warning' : 'secondary'); ?>">
                                    <?php echo ucfirst($booking['booking_status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">
                                <strong><i class="bi bi-hash"></i> Reference:</strong> 
                                <?php echo htmlspecialchars($booking['booking_reference'] ?? 'N/A'); ?>
                            </p>
                            <p class="mb-2">
                                <strong><i class="bi bi-geo-alt"></i> Location:</strong> 
                                <?php echo htmlspecialchars($booking['address'] ?? 'N/A'); ?>, <?php echo htmlspecialchars($booking['city'] ?? ''); ?>
                            </p>
                            <p class="mb-2">
                                <strong><i class="bi bi-calendar-event"></i> Move-in Date:</strong> 
                                <?php echo $booking['move_in_date'] ? date('M d, Y', strtotime($booking['move_in_date'])) : 'N/A'; ?>
                            </p>
                            <p class="mb-2">
                                <strong><i class="bi bi-cash"></i> Total Amount:</strong> 
                                <span class="text-primary">₱<?php echo number_format(floatval($booking['total_amount']), 2); ?></span>
                            </p>
                            <p class="mb-2">
                                <strong><i class="bi bi-credit-card"></i> Payment Status:</strong> 
                                <span class="badge bg-<?php echo $booking['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($booking['payment_status']); ?>
                                </span>
                            </p>
                            <?php if ($booking['payment_reference']): ?>
                                <p class="mb-2">
                                    <strong><i class="bi bi-receipt"></i> Payment Ref:</strong> 
                                    <?php echo htmlspecialchars($booking['payment_reference']); ?>
                                </p>
                            <?php endif; ?>
                            <p class="mb-2">
                                <strong><i class="bi bi-person"></i> Landlord:</strong> 
                                <?php echo htmlspecialchars($booking['landlord_name'] ?? 'N/A'); ?>
                                <?php if ($booking['landlord_phone']): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($booking['landlord_phone']); ?></small>
                                <?php endif; ?>
                            </p>
                            <p class="mb-0 text-muted">
                                <small><i class="bi bi-clock"></i> Booked on: <?php echo date('M d, Y g:i A', strtotime($booking['created_at'])); ?></small>
                            </p>
                        </div>
                        <div class="card-footer bg-white">
                            <?php if ($booking['booking_status'] === 'pending'): ?>
                                <div class="alert alert-warning mb-0 py-2">
                                    <i class="bi bi-hourglass-split"></i> Waiting for landlord confirmation
                                </div>
                            <?php elseif ($booking['booking_status'] === 'confirmed'): ?>
                                <div class="alert alert-success mb-0 py-2">
                                    <i class="bi bi-check-circle"></i> Booking confirmed!
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>