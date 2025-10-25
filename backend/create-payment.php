<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['student', 'admin']);

$booking_ref = trim($_GET['ref'] ?? '');

if ($booking_ref === '') {
    die('Invalid booking reference - no reference provided');
}

// First, let's check if the booking exists at all
$debug_stmt = $conn->prepare('SELECT id, booking_reference, user_id, payment_status FROM bookings WHERE booking_reference = ?');
$debug_stmt->bind_param('s', $booking_ref);
$debug_stmt->execute();
$debug_result = $debug_stmt->get_result();
$debug_booking = $debug_result->fetch_assoc();

if (!$debug_booking) {
    error_log("Payment page - Booking not found in database with ref: '$booking_ref'");
    
    // Let's see all bookings for this user
    $all_stmt = $conn->prepare('SELECT id, booking_reference, user_id FROM bookings WHERE user_id = ?');
    $all_stmt->bind_param('i', $_SESSION['user']['id']);
    $all_stmt->execute();
    $all_bookings = $all_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    error_log("Available bookings for user: " . json_encode($all_bookings));
    
    flash('error', 'Booking not found. Please check your bookings list.');
    header('Location: /board-in/student/my-bookings.php');
    exit;
}

if ($debug_booking['user_id'] != $_SESSION['user']['id']) {
    error_log("Payment page - User mismatch. Booking user: {$debug_booking['user_id']}, Current user: {$_SESSION['user']['id']}");
    flash('error', 'You do not have permission to pay for this booking');
    header('Location: /board-in/student/my-bookings.php');
    exit;
}

// Get full booking details
$stmt = $conn->prepare('
    SELECT b.*, 
           bh.title, 
           bh.address, 
           bh.image,
           bh.user_id as landlord_user_id,
           u.full_name AS landlord_name 
    FROM bookings b 
    LEFT JOIN boarding_houses bh ON bh.id = b.bh_id 
    LEFT JOIN users u ON u.id = bh.user_id 
    WHERE b.booking_reference = ? AND b.user_id = ? 
    LIMIT 1
');
$stmt->bind_param('si', $booking_ref, $_SESSION['user']['id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
if (!$booking) {
    error_log("Payment page - Full booking query failed for ref: '$booking_ref'");
    flash('error', 'Could not load booking details');
    header('Location: /board-in/student/my-bookings.php');
    exit;
}

if ($booking['payment_status'] === 'paid') {
    flash('info', 'This booking has already been paid');
    header('Location: /board-in/student/booking-details.php?id=' . $booking['id']);
    exit;
}

// If landlord_id is null, update it from boarding house
if (empty($booking['landlord_id']) && !empty($booking['landlord_user_id'])) {
    $update_stmt = $conn->prepare('UPDATE bookings SET landlord_id = ? WHERE id = ?');
    $update_stmt->bind_param('ii', $booking['landlord_user_id'], $booking['id']);
    $update_stmt->execute();
    $booking['landlord_id'] = $booking['landlord_user_id'];
}

header('Location: payment-success.php');

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.payment-method-card {
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 15px;
}

.payment-method-card:hover {
    border-color: #0d6efd;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
    transform: translateY(-2px);
}

.payment-method-card.selected {
    border-color: #0d6efd;
    background-color: #f0f7ff;
}

.payment-logo {
    width: 60px;
    height: 40px;
    object-fit: contain;
}

.payment-info {
    font-size: 0.85rem;
    color: #666;
}

.booking-summary-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 25px;
}

.amount-display {
    font-size: 2.5rem;
    font-weight: 700;
}

.simulation-badge {
    background: #ffc107;
    color: #000;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 15px;
}
</style>

<div class="container mt-4 mb-5">
    <?php if (PAYMENT_SIMULATION_MODE): ?>
    <div class="alert alert-warning text-center">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>SIMULATION MODE</strong> - This is a demo environment for testing purposes only. No real payments will be processed.
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Booking Summary -->
        <div class="col-md-5">
            <div class="booking-summary-card shadow-lg mb-4">
                <h5 class="mb-3"><i class="bi bi-receipt me-2"></i>Booking Summary</h5>
                
                <?php if ($booking['image']): ?>
                    <img src="/board-in/<?php echo esc_attr($booking['image']); ?>" 
                         class="img-fluid rounded mb-3" 
                         alt="Property" 
                         style="max-height: 200px; width: 100%; object-fit: cover;">
                <?php endif; ?>
                
                <div class="mb-3">
                    <small class="text-white-50">Reference Number</small>
                    <h6 class="mb-0"><?php echo esc_attr($booking['booking_reference']); ?></h6>
                </div>
                
                <div class="mb-3">
                    <small class="text-white-50">Property</small>
                    <h6 class="mb-0"><?php echo esc_attr($booking['title']); ?></h6>
                    <small><?php echo esc_attr($booking['address']); ?></small>
                </div>
                
                <div class="mb-3">
                    <small class="text-white-50">Landlord</small>
                    <h6 class="mb-0"><?php echo esc_attr($booking['landlord_name'] ?? 'N/A'); ?></h6>
                </div>
                
                <div class="mb-3">
                    <small class="text-white-50">Move-in Date</small>
                    <h6 class="mb-0"><?php echo date('F d, Y', strtotime($booking['move_in_date'])); ?></h6>
                </div>
                
                <hr class="border-white">
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Monthly Rent:</span>
                    <strong>₱<?php echo number_format($booking['monthly_rent'], 2); ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Security Deposit:</span>
                    <strong>₱<?php echo number_format($booking['security_deposit'], 2); ?></strong>
                </div>
                
                <hr class="border-white">
                
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Total Amount:</h5>
                    <div class="amount-display">₱<?php echo number_format($booking['total_amount'], 2); ?></div>
                </div>
            </div>
            
            <!-- Security Badge -->
            <div class="alert alert-success">
                <i class="bi bi-shield-check me-2"></i>
                <strong><?php echo PAYMENT_SIMULATION_MODE ? 'Demo Mode' : 'Secure Payment'; ?></strong><br>
                <small><?php echo PAYMENT_SIMULATION_MODE ? 'Testing environment - No real transactions' : 'Your payment is protected and encrypted'; ?></small>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h4 class="mb-0"><i class="bi bi-credit-card me-2"></i>Select Payment Method</h4>
                    <?php if (PAYMENT_SIMULATION_MODE): ?>
                    <span class="simulation-badge mt-2">
                        <i class="bi bi-lightning-fill me-1"></i>DEMO MODE
                    </span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="/board-in/backend/create-payment.php?ref=<?= esc_attr($booking['booking_reference']); ?>" id="paymentForm">
                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                        <input type="hidden" name="booking_reference" value="<?php echo esc_attr($booking['booking_reference']); ?>">
                        <input type="hidden" name="amount" value="<?php echo $booking['total_amount']; ?>">
                        <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="">

                        <!-- GCash -->
                        <?php if (ENABLE_GCASH): ?>
                        <div class="payment-method-card" data-method="gcash">
                            <div class="d-flex align-items-center">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5d/GCash_logo.svg/1200px-GCash_logo.svg.png" 
                                     class="payment-logo me-3" alt="GCash">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">GCash <?php echo PAYMENT_SIMULATION_MODE ? '<span class="badge bg-warning text-dark ms-2">DEMO</span>' : ''; ?></h6>
                                    <p class="payment-info mb-0">Pay using your GCash wallet instantly</p>
                                </div>
                                <i class="bi bi-circle" style="font-size: 1.5rem; color: #ddd;"></i>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- PayMaya -->
                        <?php if (ENABLE_PAYMAYA): ?>
                        <div class="payment-method-card" data-method="paymaya">
                            <div class="d-flex align-items-center">
                                <img src="https://www.paymaya.com/assets/images/paymaya-logo.svg" 
                                     class="payment-logo me-3" alt="PayMaya">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">PayMaya <?php echo PAYMENT_SIMULATION_MODE ? '<span class="badge bg-warning text-dark ms-2">DEMO</span>' : ''; ?></h6>
                                    <p class="payment-info mb-0">Pay using your PayMaya account</p>
                                </div>
                                <i class="bi bi-circle" style="font-size: 1.5rem; color: #ddd;"></i>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- GrabPay -->
                        <?php if (ENABLE_GRAB_PAY): ?>
                        <div class="payment-method-card" data-method="grabpay">
                            <div class="d-flex align-items-center">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/8/84/GrabPay_logo.svg" 
                                     class="payment-logo me-3" alt="GrabPay">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">GrabPay <?php echo PAYMENT_SIMULATION_MODE ? '<span class="badge bg-warning text-dark ms-2">DEMO</span>' : ''; ?></h6>
                                    <p class="payment-info mb-0">Pay using your GrabPay wallet</p>
                                </div>
                                <i class="bi bi-circle" style="font-size: 1.5rem; color: #ddd;"></i>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Credit/Debit Card -->
                        <?php if (ENABLE_CARD): ?>
                        <div class="payment-method-card" data-method="card">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-credit-card payment-logo me-3" style="font-size: 2.5rem;"></i>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Credit/Debit Card <?php echo PAYMENT_SIMULATION_MODE ? '<span class="badge bg-warning text-dark ms-2">DEMO</span>' : ''; ?></h6>
                                    <p class="payment-info mb-0">Visa, Mastercard, JCB, American Express</p>
                                </div>
                                <i class="bi bi-circle" style="font-size: 1.5rem; color: #ddd;"></i>
                            </div>
                        </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mt-4" id="payBtn" disabled>
                            <i class="bi bi-lock-fill me-2"></i><?php echo PAYMENT_SIMULATION_MODE ? 'Simulate Payment' : 'Proceed to Payment'; ?>
                        </button>
                    </form>

                    <div class="mt-4 text-center">
                        <a href="/board-in/student/my-bookings.php" class="btn btn-link">
                            <i class="bi bi-arrow-left me-1"></i>Back to My Bookings
                        </a>
                    </div>
                </div>
            </div>

            <!-- Payment Instructions -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-info-circle me-2"></i><?php echo PAYMENT_SIMULATION_MODE ? 'Demo Instructions' : 'Payment Instructions'; ?></h6>
                    <?php if (PAYMENT_SIMULATION_MODE): ?>
                    <ol class="mb-0 small">
                        <li>Select your preferred payment method above</li>
                        <li>Click "Simulate Payment" button</li>
                        <li>You'll see a simulated payment gateway screen</li>
                        <li>Choose to simulate success or failure</li>
                        <li>The system will process as if a real payment occurred</li>
                    </ol>
                    <?php else: ?>
                    <ol class="mb-0 small">
                        <li>Select your preferred payment method above</li>
                        <li>Click "Proceed to Payment" button</li>
                        <li>You'll be redirected to the payment gateway</li>
                        <li>Complete the payment securely</li>
                        <li>You'll receive a confirmation after successful payment</li>
                    </ol>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentCards = document.querySelectorAll('.payment-method-card');
    const paymentMethodInput = document.getElementById('selectedPaymentMethod');
    const payBtn = document.getElementById('payBtn');
    const form = document.getElementById('paymentForm');
    
    // Handle payment method selection
    paymentCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove selected class from all cards
            paymentCards.forEach(c => {
                c.classList.remove('selected');
                c.querySelector('.bi-circle').classList.remove('bi-check-circle-fill');
                c.querySelector('.bi-circle').classList.add('bi-circle');
                c.querySelector('.bi-circle').style.color = '#ddd';
            });
            
            // Add selected class to clicked card
            this.classList.add('selected');
            const icon = this.querySelector('.bi-circle');
            icon.classList.remove('bi-circle');
            icon.classList.add('bi-check-circle-fill');
            icon.style.color = '#0d6efd';
            
            // Set payment method
            const method = this.dataset.method;
            paymentMethodInput.value = method;
            payBtn.disabled = false;
        });
    });
    
    // Prevent double submission
    form.addEventListener('submit', function() {
        payBtn.disabled = true;
        payBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>