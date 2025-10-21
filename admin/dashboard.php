<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getDB();

// Handle approve/reject directly from dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listing_id = $_POST['listing_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($listing_id && in_array($action, ['approved', 'rejected'])) {
        $stmt = $pdo->prepare("UPDATE listings SET status = ? WHERE id = ?");
        $stmt->execute([$action, $listing_id]);
        flash('success', "Listing has been $action successfully.");
        header("Location: dashboard.php");
        exit;
    }
}

// Fetch only pending listings
$stmt = $pdo->query("
    SELECT listings.*, users.username 
    FROM listings
    JOIN users ON listings.user_id = users.id
    WHERE listings.status = 'pending'
    ORDER BY listings.created_at DESC
");
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>🛠 Admin Dashboard</h2>
        <a href="/board-in/admin/manage-listings.php" class="btn btn-primary">📋 Manage All Listings</a>
    </div>

    <p class="text-muted">Below are landlord listings waiting for approval.</p>

    <?php if (empty($listings)): ?>
        <div class="alert alert-info">✅ No pending listings. All have been reviewed.</div>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Landlord</th>
                    <th>Price</th>
                    <th>Location</th>
                    <th>DTI Certificate</th>
                    <th>Image</th>
                    <th>Submitted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listings as $listing): ?>
                    <tr>
                        <td><?= htmlspecialchars($listing['id']) ?></td>
                        <td><?= htmlspecialchars($listing['title']) ?></td>
                        <td><?= htmlspecialchars($listing['username']) ?></td>
                        <td>₱<?= number_format($listing['price'], 2) ?></td>
                        <td><?= htmlspecialchars($listing['location']) ?></td>
                        <td>
                            <?php if (!empty($listing['dti_certificate'])): ?>
                                <a href="<?= htmlspecialchars($listing['dti_certificate']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">View</a>
                            <?php else: ?>
                                <em>No DTI</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($listing['image'])): ?>
                                <img src="<?= htmlspecialchars($listing['image']) ?>" alt="Listing" width="70" style="object-fit:cover;">
                            <?php else: ?>
                                <em>No Image</em>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($listing['created_at'] ?? 'N/A') ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
                                <input type="hidden" name="action" value="approved">
                                <button class="btn btn-sm btn-success">Approve</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
                                <input type="hidden" name="action" value="rejected">
                                <button class="btn btn-sm btn-danger">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
