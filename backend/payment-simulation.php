<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['student', 'admin']);

// Check if we have simulation data
if (!isset($_SESSION['simulation_payment'])) {
    flash('error', 'Invalid payment session');
    header('Location: /board-in/student/my-bookings.php');
    exit;
}

$payment = $_SESSION['simulation_payment'];
require_once __DIR__ . '/../includes/header.php';
?>

<style>
.simulation-container {
    max-width: 600px;
    margin: 50px auto;
}

.payment-gateway-mockup {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    padding: 40px;
}

.gateway-header {
    text-align: center;
    padding-bottom: 30px;
    border-bottom: 2px solid #f0f0f0;
    margin-bottom: 30px;
}

.payment-method-logo {
    width: 120px;
    height: 80px;
    object-fit: contain;
    margin-bottom: 15px;
}

.amount-display {
    font-size: 3rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 20px 0;
}

.merchant-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.simulation-badge {
    background: #ffc107;
    color: #000;
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 20px;
}

.btn-simulate {
    padding: 15px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 10px;
    transition: all 0.3s;
}

.btn-simulate:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #e9ecef;
}

.info-item:last-child {
    border-bottom: none;
}
</style>

<div class="container simulation-container">
    <div class="payment-gateway-mockup">
        <div class="gateway-header">
            <span class="simulation-badge">
                <i class="bi bi-lightning-fill me-2"></i>SIMULATION MODE
            </span>
            
            <?php
            $logos = [
                'gcash' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5d/GCash_logo.svg/1200px-GCash_logo.svg.png',
                'paymaya' => 'https://www.paymaya.com/assets/images/paymaya-logo.svg',
                'grabpay' => 'https://upload.wikimedia.org/wikipedia/commons/8/84/GrabPay_logo.svg',
                'card' => null
            ];
            
            if ($payment['method'] === 'card'):
            ?>
                <i class="bi bi-credit-card" style="font-size: 4rem; color: #0d6efd;"></i>
                <h4 class="mt-3">Card Payment Gateway</h4>
            <?php else: ?>
                <img src="<?php echo $logos[$payment['method']]; ?>" 
                     class="payment-method-logo" 
                     alt="<?php echo strtoupper($payment['method']); ?>">
                <h4><?php echo strtoupper($payment['method']); ?> Payment</h4>
            <?php endif; ?>
        </div>

        <div class="merchant-info">
            <h6 class="text-muted mb-3">Payment Details</h6>
            
            <div class="info-item">
                <span class="text-muted">Merchant:</span>
                <strong>Board-In Booking System</strong>
            </div>
            
            <div class="info-item">
                <span class="text-muted">Property:</span>
                <strong><?php echo esc_attr($payment['property']); ?></strong>
            </div>
            
            <div class="info-item">
                <span class="text-muted">Reference:</span>
                <strong><?php echo esc_attr($payment['booking_ref']); ?></strong>
            </div>
            
            <div class="info-item">
                <span class="text-muted">Payment ID:</span>
                <strong><?php echo esc_attr($payment['reference']); ?></strong>
            </div>
        </div>

        <div class="text-center">
            <p class="text-muted mb-2">Amount to Pay</p>
            <div class="amount-display">₱<?php echo number_format($payment['amount'], 2); ?></div>
        </div>

        <div class="alert alert-info mt-4">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Test Mode:</strong> Choose an action below to simulate the payment result. No actual payment will be processed.
        </div>

        <form method="POST" action="/board-in/backend/simulate-payment-result.php" id="simulationForm">
            <input type="hidden" name="booking_id" value="<?php echo $payment['booking_id']; ?>">
            <input type="hidden" name="payment_reference" value="<?php echo $payment['reference']; ?>">
            <input type="hidden" name="amount" value="<?php echo $payment['amount']; ?>">
            <input type="hidden" name="result" id="paymentResult" value="">

            <div class="d-grid gap-3 mt-4">
                <button type="button" class="btn btn-success btn-simulate btn-lg" onclick="simulatePayment('success')">
                    <i class="bi bi-check-circle me-2"></i>Simulate Successful Payment
                </button>
                
                <button type="button" class="btn btn-danger btn-simulate btn-lg" onclick="simulatePayment('failed')">
                    <i class="bi bi-x-circle me-2"></i>Simulate Failed Payment
                </button>
                
                <a href="/board-in/student/my-bookings.php" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-arrow-left me-2"></i>Cancel
                </a>
            </div>
        </form>

        <div class="mt-4 text-center">
            <small class="text-muted">
                <i class="bi bi-shield-check me-1"></i>
                This is a demo environment for testing purposes only
            </small>
        </div>
    </div>
</div>

<script>
function simulatePayment(result) {
    if (!confirm(`Are you sure you want to simulate a ${result} payment?`)) {
        return;
    }
    
    document.getElementById('paymentResult').value = result;
    const form = document.getElementById('simulationForm');
    const buttons = form.querySelectorAll('button[type="button"]');
    
    buttons.forEach(btn => {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    });
    
    // Simulate processing delay
    setTimeout(() => {
        form.submit();
    }, 1500);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>