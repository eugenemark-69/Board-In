<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getDB();

// Fetch only APPROVED listings
$stmt = $pdo->query("
    SELECT listings.*, users.username 
    FROM listings
    JOIN users ON listings.user_id = users.id
    WHERE listings.status = 'approved'
    ORDER BY listings.created_at DESC
");

$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2>Available Boarding Houses</h2>

    <?php if (empty($listings)): ?>
        <div class="alert alert-info mt-3">No approved listings available at the moment.</div>
    <?php else: ?>
        <div class="row mt-3">
            <?php foreach ($listings as $listing): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <?php if (!empty($listing['image'])): ?>
                            <img src="<?= htmlspecialchars($listing['image']) ?>" class="card-img-top" alt="Listing Image" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                                <span>No Image</span>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($listing['title']) ?></h5>
                            <p class="card-text">
                                <strong>Location:</strong> <?= htmlspecialchars($listing['location']) ?><br>
                                <strong>Price:</strong> ₱<?= htmlspecialchars(number_format($listing['price'], 2)) ?><br>
                                <strong>Amenities:</strong> <?= htmlspecialchars($listing['amenities']) ?>
                            </p>
                            <a href="view-booking.php?id=<?= $listing['id'] ?>" class="btn btn-primary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
