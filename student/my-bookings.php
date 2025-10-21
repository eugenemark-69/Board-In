<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['student', 'admin']);
require_once __DIR__ . '/../includes/header.php';

$conn = getDB();
$student_id = $_SESSION['user']['id'] ?? 0;

// ✅ Fetch bookings for the student
$stmt = $conn->prepare("
    SELECT b.*, l.title 
    FROM bookings b 
    LEFT JOIN listings l ON l.id = b.listing_id 
    WHERE b.student_id = ? 
    ORDER BY b.created_at DESC
");
$stmt->execute([$student_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>My Bookings</h2>

<?php if (isset($_GET['success'])): ?>
  <div class="alert alert-success mt-3">
    ✅ Booking successfully created!
  </div>
<?php endif; ?>

<?php if ($bookings): ?>
    <?php foreach ($bookings as $b): ?>
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <h5>
                    <a href="/board-in/student/view-booking.php?id=<?php echo $b['id']; ?>">
                    <?php echo htmlspecialchars($b['title']); ?>
                    </a>
                    — <small><?php echo htmlspecialchars($b['status'] ?? 'Pending'); ?></small>
                </h5>

                <p>
                    <strong>Booking ID:</strong> <?php echo htmlspecialchars($b['id']); ?><br>
                    <strong>Created at:</strong> <?php echo htmlspecialchars($b['created_at']); ?><br>
                </p>

                <p>
                    <strong>Status:</strong>
                    <span class="badge bg-<?php 
                        echo ($b['status'] === 'approved' ? 'success' : 
                             ($b['status'] === 'rejected' ? 'danger' : 'secondary')); ?>">
                        <?php echo ucfirst($b['status'] ?? 'pending'); ?>
                    </span>
                </p>

                <?php if (($b['payment_status'] ?? '') === 'pending'): ?>
                    <form method="post" action="/board-in/backend/process-payment-ref.php" class="d-flex gap-2">
                        <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($b['id']); ?>">
                        <input name="payment_reference" class="form-control form-control-sm" placeholder="GCash transaction ID" required>
                        <button class="btn btn-sm btn-primary">Submit Payment Reference</button>
                    </form>
                <?php elseif (!empty($b['payment_reference'])): ?>
                    <p><strong>Payment reference:</strong> <?php echo htmlspecialchars($b['payment_reference']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="alert alert-info mt-3">No bookings found.</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
