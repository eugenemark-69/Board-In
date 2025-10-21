<?php
// Start session and include required files first (no output before this)
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['student', 'admin']);

// ✅ Get database connection
$conn = getDB();

// Get and validate the listing ID
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    flash('error', 'Listing not specified');
    header('Location: /board-in/pages/search.php');
    exit;
}

// Prepare and execute SQL query
$stmt = $conn->prepare('SELECT id, title, monthly_rent, security_deposit, available_rooms FROM boarding_houses WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$listing) {
    flash('error', 'Listing not found');
    header('Location: /board-in/pages/search.php');
    exit;
}

// ✅ Only include header *after* all redirects and logic
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Book: <?php echo htmlspecialchars($listing['title']); ?></h2>

<form method="post" action="/board-in/backend/process-booking.php">
    <input type="hidden" name="listing_id" value="<?php echo htmlspecialchars($listing['id']); ?>">

    <div class="mb-3">
        <label class="form-label">Move-in date</label>
        <input name="move_in_date" type="date" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Monthly rent</label>
        <input class="form-control" value="₱<?php echo number_format($listing['monthly_rent'], 2); ?>" disabled>
    </div>

    <div class="mb-3">
        <label class="form-label">Security deposit</label>
        <input class="form-control" value="₱<?php echo number_format($listing['security_deposit'], 2); ?>" disabled>
    </div>

    <div class="mb-3">
        <p>Total: <strong>₱<?php echo number_format($listing['monthly_rent'] + $listing['security_deposit'], 2); ?></strong></p>
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="agree_terms" id="agree_terms" required>
        <label class="form-check-label" for="agree_terms">I agree to the terms and conditions</label>
    </div>

    <button class="btn btn-primary">Pay with GCash (simulate)</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
