<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['student','admin']);
require_once __DIR__ . '/../includes/header.php';

$student_id = $_SESSION['user']['id'];

$stmt = $conn->prepare('SELECT b.id AS booking_id, b.listing_id, bh.title FROM bookings b LEFT JOIN reviews r ON r.booking_id = b.id LEFT JOIN boarding_houses bh ON bh.id = b.listing_id WHERE b.student_id = ? AND (b.booking_status = "active" OR b.booking_status = "completed") AND r.id IS NULL');
$stmt->bind_param('i', $student_id);
$stmt->execute();
$res = $stmt->get_result();

?>

<h2>Write a Review</h2>
<?php if ($res->num_rows > 0): ?>
	<?php while ($b = $res->fetch_assoc()): ?>
		<div class="card mb-2">
			<div class="card-body">
				<h5><?php echo esc_attr($b['title']); ?></h5>
				<form method="post" action="/board-in/backend/process-review.php" enctype="multipart/form-data">
					<input type="hidden" name="booking_id" value="<?php echo $b['booking_id']; ?>">
					<div class="mb-2"><label class="form-label">Rating (1-5)</label><input name="rating" type="number" min="1" max="5" class="form-control" required></div>
					<div class="mb-2"><label class="form-label">Cleanliness</label><input name="cleanliness_rating" type="number" min="1" max="5" class="form-control"></div>
					<div class="mb-2"><label class="form-label">Location</label><input name="location_rating" type="number" min="1" max="5" class="form-control"></div>
					<div class="mb-2"><label class="form-label">Value</label><input name="value_rating" type="number" min="1" max="5" class="form-control"></div>
					<div class="mb-2"><label class="form-label">Landlord</label><input name="landlord_rating" type="number" min="1" max="5" class="form-control"></div>
					<div class="mb-2"><label class="form-label">Comment</label><textarea name="comment" class="form-control"></textarea></div>
					<button class="btn btn-primary">Submit review</button>
				</form>
			</div>
		</div>
	<?php endwhile; ?>
<?php else: ?>
	<p>No pending reviews. You can view past reviews below.</p>
<?php endif; ?>

<h3>Your past reviews</h3>
<?php
$stmt2 = $conn->prepare('SELECT r.*, bh.title FROM reviews r LEFT JOIN boarding_houses bh ON bh.id = r.listing_id WHERE r.student_id = ? ORDER BY r.created_at DESC');
$stmt2->bind_param('i', $student_id);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($rv = $res2->fetch_assoc()): ?>
	<div class="card mb-2">
		<div class="card-body">
			<h5><?php echo esc_attr($rv['title']); ?></h5>
			<p>Rating: <?php echo esc_attr($rv['rating']); ?>/5</p>
			<p><?php echo nl2br(esc_attr($rv['comment'])); ?></p>
		</div>
	</div>
<?php endwhile; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
