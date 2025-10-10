<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
	flash('error', 'Listing not found');
	header('Location: /board-in/pages/search.php');
	exit;
}

$stmt = $conn->prepare('SELECT bh.*, u.full_name AS landlord_name, u.contact_number AS landlord_contact FROM boarding_houses bh LEFT JOIN users u ON u.id = bh.manager_id WHERE bh.id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$listing = $res->fetch_assoc();
if (!$listing) {
	flash('error', 'Listing not found');
	header('Location: /board-in/pages/search.php');
	exit;
}

// photos
$photos = [];
$stmt2 = $conn->prepare('SELECT photo_url, is_primary FROM photos WHERE boarding_house_id = ? ORDER BY is_primary DESC, id ASC');
$stmt2->bind_param('i', $id);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($r = $res2->fetch_assoc()) $photos[] = $r;

// amenities
$amen = [];
$stmt3 = $conn->prepare('SELECT * FROM amenities WHERE boarding_house_id = ? LIMIT 1');
$stmt3->bind_param('i', $id);
$stmt3->execute();
$res3 = $stmt3->get_result();
if ($res3) $amen = $res3->fetch_assoc();

// reviews (basic)
$reviews = [];
$stmt4 = $conn->prepare('SELECT r.*, u.full_name FROM reviews r LEFT JOIN users u ON u.id = r.student_id WHERE r.listing_id = ? ORDER BY r.created_at DESC');
$stmt4->bind_param('i', $id);
$stmt4->execute();
$res4 = $stmt4->get_result();
while ($r = $res4->fetch_assoc()) $reviews[] = $r;

?>

<div class="row">
	<div class="col-md-8">
		<h2><?php echo esc_attr($listing['title']); ?></h2>
		<p><strong><?php echo esc_attr($listing['name'] ?: $listing['title']); ?></strong></p>
		<div class="mb-3">
			<?php if (!empty($photos)): ?>
				<?php foreach ($photos as $p): ?>
					<img src="<?php echo esc_attr($p['photo_url']); ?>" class="img-fluid mb-2" style="max-height:300px; object-fit:cover; width:100%;">
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<p><strong>Price:</strong> ₱<?php echo number_format($listing['monthly_rent'], 2); ?> / month</p>
		<p><strong>Address:</strong> <?php echo nl2br(esc_attr($listing['address'])); ?></p>
		<p><strong>Availability:</strong> <?php echo esc_attr($listing['available_rooms']); ?> available of <?php echo esc_attr($listing['total_rooms']); ?></p>
		<h5>Amenities</h5>
		<ul>
			<?php if ($amen): ?>
				<?php if ($amen['wifi']): ?><li>WiFi</li><?php endif; ?>
				<?php if ($amen['own_cr']): ?><li>Own CR</li><?php endif; ?>
				<?php if ($amen['shared_kitchen']): ?><li>Shared kitchen</li><?php endif; ?>
				<?php if ($amen['laundry_area']): ?><li>Laundry area</li><?php endif; ?>
				<?php if ($amen['parking']): ?><li>Parking</li><?php endif; ?>
				<?php if ($amen['study_area']): ?><li>Study area</li><?php endif; ?>
				<?php if ($amen['air_conditioning']): ?><li>Air conditioning</li><?php endif; ?>
				<?php if ($amen['water_heater']): ?><li>Water heater</li><?php endif; ?>
			<?php else: ?>
				<li>No amenities listed</li>
			<?php endif; ?>
		</ul>
		<h5>House rules</h5>
		<p><?php echo nl2br(esc_attr($listing['house_rules'])); ?></p>
		<h5>Description</h5>
		<p><?php echo nl2br(esc_attr($listing['description'])); ?></p>

		<h5>Reviews</h5>
		<?php if (!empty($reviews)): ?>
			<?php foreach ($reviews as $rv): ?>
				<div class="border p-2 mb-2">
					<strong><?php echo esc_attr($rv['full_name'] ?? 'Student'); ?></strong>
					<div>Rating: <?php echo esc_attr($rv['rating']); ?>/5</div>
					<p><?php echo nl2br(esc_attr($rv['comment'])); ?></p>
				</div>
			<?php endforeach; ?>
		<?php else: ?>
			<p>No reviews yet.</p>
		<?php endif; ?>
	</div>
	<div class="col-md-4">
		<div class="card">
			<div class="card-body">
				<h5>Contact</h5>
				<p><?php echo esc_attr($listing['landlord_name']); ?></p>
				<p><?php echo esc_attr($listing['landlord_contact']); ?></p>
				<a href="/board-in/student/booking.php?id=<?php echo $listing['id']; ?>" class="btn btn-primary">Book Now</a>
			</div>
		</div>
	</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
