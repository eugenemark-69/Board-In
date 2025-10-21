<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';

// Check session
if (!isset($_SESSION['user'])) {
    die("Not logged in");
}

if ($_SESSION['user']['user_type'] !== 'landlord') {
    die("Not a landlord");
}

$pdo = getDB();
$user_id = $_SESSION['user']['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM listings WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $listings = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Listings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>My Listings</h2>
        <a href="add-listing.php" class="btn btn-primary mb-3">Add New Listing</a>
        
        <?php if (empty($listings)): ?>
            <div class="alert alert-info">No listings found.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($listings as $listing): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <?php if ($listing['image']): ?>
                                <img src="../<?php echo htmlspecialchars($listing['image']); ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5><?php echo htmlspecialchars($listing['title']); ?></h5>
                                <p>₱<?php echo number_format($listing['price']); ?> / month</p>
                                <p><?php echo htmlspecialchars($listing['location']); ?></p>
                                
                                <?php if ($listing['status'] === 'pending'): ?>
                                    <span class="badge bg-warning">Pending</span>
                                <?php elseif ($listing['status'] === 'approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php elseif ($listing['status'] === 'rejected'): ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>