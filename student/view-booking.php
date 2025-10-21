<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['student', 'admin']);
require_once __DIR__ . '/../includes/header.php';

$conn = getDB();

$id = intval($_GET['id'] ?? 0);
$student_id = $_SESSION['user']['id'] ?? 0;

if ($id <= 0) {
    echo "<p>Invalid booking ID.</p>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// ✅ Fetch booking details
$stmt = $conn->prepare("
    SELECT b.*, l.title, l.price, l.location, l.description
    FROM bookings b
    LEFT JOIN listings l ON l.id = b.listing_id
    WHERE b.id = ? AND b.student_id = ?
");
$stmt->execute([$id, $student_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo "<p>Booking not found.</p>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($booking['title']); ?></h2>
    <p><strong>Booking ID:</strong> <?php echo $booking['id']; ?></p>
    <p><strong>Created at:</strong> <?php echo htmlspecialchars($booking['created_at']); ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($booking['status'] ?? 'Pending'); ?></p>
    <p><strong>Payment reference:</strong> <?php echo htmlspecialchars($booking['payment_reference'] ?? 'N/A'); ?></p>

    <hr>

    <h5>Listing Details</h5>
    <p><strong>Price:</strong> ₱<?php echo number_format($booking['price'], 2); ?> / month</p>
    <p><strong>Location:</strong> <?php echo htmlspecialchars($booking['location']); ?></p>
    <p><?php echo nl2br(htmlspecialchars($booking['description'])); ?></p>

    <a href="/board-in/student/my-bookings.php" class="btn btn-secondary mt-3">← Back to My Bookings</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
