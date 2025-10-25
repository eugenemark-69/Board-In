<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['student', 'admin']);
require_once __DIR__ . '/../includes/header.php';

$booking_id = intval($_GET['booking_id'] ?? 0);
$client_key = $_SESSION['payment_intent_client_key'] ?? '';

if (!$client_key || !$booking_id) {
    flash('error', 'Invalid payment session');
    header('Location: /board-in/student/my-bookings.php');
    exit;
}

// Get booking details
$stmt = $conn->prepare('SELECT * FROM bookings WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $booking_id, $_SESSION['user']['id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    flash('error', 'Booking not found');
    header('Location: /board-in/student/my-bookings.php');
    exit;
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-credit-card me-2"></i>Card Payment</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Amount to Pay:</strong> ₱<?php echo number_format($booking['total_amount'], 2); ?>
                    </div>

                    <!-- Card Payment Form -->
                    <form id="payment-form">
                        <div class="mb-3">
                            <label class="form-label">Card Information</label>
                            <div id="card-element" class="form-control" style="height: auto; padding: 12px;">
                                <!-- PayMongo card element will be inserted here -->
                            </div>
                            <div id="card-errors" class="text-danger mt-2"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cardholder Name</label>
                                <input type="text" id="card-holder-name" class="form-control" 
                                       value="<?php echo esc_attr($_SESSION['user']['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" id="card-holder-email" class="form-control" 
                                       value="<?php echo esc_attr($_SESSION['user']['email']); ?>" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-button">
                            <i class="bi bi-lock-fill me-2"></i>Pay ₱<?php echo number_format($booking['total_amount'], 2); ?>
                        </button>
                    </form>

                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            <i class="bi bi-shield-lock me-1"></i>Your payment is secure and encrypted
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PayMongo.js SDK -->
<script src="https://js.paymongo.com/v1/paymongo.js"></script>
<script>
const paymongo = Paymongo('<?php echo PAYMONGO_PUBLIC_KEY; ?>');
const clientKey = '<?php echo $client_key; ?>';
const bookingId = <?php echo $booking_id; ?>;

// Create card element
const cardElement = paymongo.elements.create('card', {
    style: {
        base: {
            fontSize: '16px',
            color: '#32325d',
            fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    }
});

cardElement.mount('#card-element');

// Handle form submission
const form = document.getElementById('payment-form');
const submitButton = document.getElementById('submit-button');

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    
    const cardHolderName = document.getElementById('card-holder-name').value;
    const cardHolderEmail = document.getElementById('card-holder-email').value;
    
    try {
        const paymentMethod = await paymongo.createPaymentMethod({
            type: 'card',
            details: {
                card_number: cardElement.cardNumber,
                exp_month: cardElement.expMonth,
                exp_year: cardElement.expYear,
                cvc: cardElement.cvc
            },
            billing: {
                name: cardHolderName,
                email: cardHolderEmail
            }
        });
        
        // Attach payment method to intent
        const result = await paymongo.attachPaymentIntent(clientKey, {
            payment_method: paymentMethod.id,
            return_url: window.location.origin + '/board-in/backend/payment-success.php?booking_id=' + bookingId
        });
        
        if (result.status === 'succeeded') {
            window.location.href = '/board-in/backend/payment-success.php?booking_id=' + bookingId;
        } else if (result.next_action) {
            // 3D Secure authentication required
            window.location.href = result.next_action.redirect.url;
        }
        
    } catch (error) {
        document.getElementById('card-errors').textContent = error.message;
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="bi bi-lock-fill me-2"></i>Pay ₱<?php echo number_format($booking['total_amount'], 2); ?>';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>