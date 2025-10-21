<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

$conn = getDB();
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo "<p>Listing not found.</p>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// ✅ Fetch listing info
$stmt = $conn->prepare("SELECT * FROM listings WHERE id = ?");
$stmt->execute([$id]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$listing) {
    echo "<p>Listing not found.</p>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// ✅ Fetch photos
$stmt2 = $conn->prepare("SELECT photo_url, is_primary FROM photos WHERE boarding_house_id = ? ORDER BY is_primary DESC, id ASC");
$stmt2->execute([$id]);
$photos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// ✅ Fetch amenities
$stmt3 = $conn->prepare("SELECT * FROM amenities WHERE boarding_house_id = ? LIMIT 1");
$stmt3->execute([$id]);
$amen = $stmt3->fetch(PDO::FETCH_ASSOC);

// ✅ Fetch reviews
$stmt4 = $conn->prepare("SELECT r.*, u.full_name 
                         FROM reviews r 
                         LEFT JOIN users u ON u.id = r.student_id 
                         WHERE r.listing_id = ? 
                         ORDER BY r.created_at DESC");
$stmt4->execute([$id]);
$reviews = $stmt4->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-8">
        <h2><?php echo htmlspecialchars($listing['title']); ?></h2>

        <div class="mb-3">
            <?php if (!empty($listing['image'])): ?>
                <img src="<?php echo htmlspecialchars($listing['image']); ?>" class="img-fluid" alt="Boarding House">
            <?php else: ?>
                <img src="/board-in/assets/images/boardinghouse.jpg" class="img-fluid" alt="Default Image">
            <?php endif; ?>
        </div>

        <p><strong>Price:</strong> ₱<?php echo number_format($listing['price'], 2); ?> / month</p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($listing['location']); ?></p>

        <h5>Description</h5>
        <p><?php echo nl2br(htmlspecialchars($listing['description'])); ?></p>

        <h5>Amenities</h5>
        <ul>
            <?php if ($amen): ?>
                <?php if (!empty($amen['wifi'])): ?><li>WiFi</li><?php endif; ?>
                <?php if (!empty($amen['own_cr'])): ?><li>Own CR</li><?php endif; ?>
                <?php if (!empty($amen['shared_kitchen'])): ?><li>Shared Kitchen</li><?php endif; ?>
                <?php if (!empty($amen['laundry_area'])): ?><li>Laundry Area</li><?php endif; ?>
                <?php if (!empty($amen['parking'])): ?><li>Parking</li><?php endif; ?>
                <?php if (!empty($amen['study_area'])): ?><li>Study Area</li><?php endif; ?>
                <?php if (!empty($amen['air_conditioning'])): ?><li>Air Conditioning</li><?php endif; ?>
                <?php if (!empty($amen['water_heater'])): ?><li>Water Heater</li><?php endif; ?>
            <?php else: ?>
                <li>No amenities listed</li>
            <?php endif; ?>
        </ul>

        <h5>Reviews</h5>
        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $rv): ?>
                <div class="border p-2 mb-2">
                    <strong><?php echo htmlspecialchars($rv['full_name'] ?? 'Student'); ?></strong>
                    <div>Rating: <?php echo htmlspecialchars($rv['rating']); ?>/5</div>
                    <p><?php echo nl2br(htmlspecialchars($rv['comment'])); ?></p>
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
            <p><?php echo htmlspecialchars($listing['landlord_name'] ?? 'N/A'); ?></p>
            <p><?php echo htmlspecialchars($listing['landlord_contact'] ?? 'N/A'); ?></p>

            <form method="post" action="/board-in/backend/book-listing.php">
                <input type="hidden" name="listing_id" value="<?php echo htmlspecialchars($listing['id']); ?>">
                <button type="submit" class="btn btn-primary w-100">Book Now</button>
            </form>
        </div>
    </div>
</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
