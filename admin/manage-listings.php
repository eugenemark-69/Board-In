<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role(['admin']);

// Handle approve/reject/delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid security token. Please try again.';
        header('Location: /board-in/admin/manage-listings.php');
        exit;
    }

    $listing_id = isset($_POST['listing_id']) ? (int)$_POST['listing_id'] : 0;
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($listing_id > 0) {
        if (in_array($action, ['active', 'rejected', 'pending', 'inactive'])) {
            // Update status
            $stmt = $conn->prepare("UPDATE boarding_houses SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $action, $listing_id);
            
            if ($stmt->execute()) {
                // Get listing owner and send notification
                $stmt2 = $conn->prepare("SELECT user_id, title FROM boarding_houses WHERE id = ?");
                $stmt2->bind_param("i", $listing_id);
                $stmt2->execute();
                $result = $stmt2->get_result();
                $listing = $result->fetch_assoc();
                
                if ($listing) {
                    $message = "Your listing '{$listing['title']}' has been " . strtolower($action) . ".";
                    $stmt3 = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
                    $title = "Listing Status Update";
                    $notif_type = $action === 'active' ? 'success' : 'warning';
                    $link = '/board-in/pages/listing.php?id=' . $listing_id;
                    $stmt3->bind_param("issss", $listing['user_id'], $title, $message, $notif_type, $link);
                    $stmt3->execute();
                }
                
                $_SESSION['success'] = "Listing has been " . ucfirst($action) . " successfully.";
            } else {
                $_SESSION['error'] = 'Error updating listing status.';
            }
        } elseif ($action === 'delete') {
            // Delete listing
            $stmt = $conn->prepare("DELETE FROM boarding_houses WHERE id = ?");
            $stmt->bind_param("i", $listing_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Listing deleted successfully.';
            } else {
                $_SESSION['error'] = 'Error deleting listing.';
            }
        }
    }
    
    header('Location: /board-in/admin/manage-listings.php' . (isset($_GET['filter']) ? '?filter=' . urlencode($_GET['filter']) : ''));
    exit;
}

// Get filter status
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';
$allowed_filters = ['all', 'pending', 'active', 'rejected', 'inactive', 'available'];
if (!in_array($filter, $allowed_filters)) {
    $filter = 'all';
}

// Fetch listings based on filter
if ($filter === 'all') {
    $stmt = $conn->prepare("
        SELECT bh.*, u.username, u.email, u.full_name, u.phone,
        (SELECT COUNT(*) FROM reviews WHERE listing_id = bh.id AND status = 'approved') as review_count,
        (SELECT AVG(rating) FROM reviews WHERE listing_id = bh.id AND status = 'approved') as avg_rating
        FROM boarding_houses bh
        LEFT JOIN users u ON bh.user_id = u.id
        ORDER BY 
            CASE WHEN bh.status = 'pending' THEN 0 ELSE 1 END,
            bh.created_at DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt = $conn->prepare("
        SELECT bh.*, u.username, u.email, u.full_name, u.phone,
        (SELECT COUNT(*) FROM reviews WHERE listing_id = bh.id AND status = 'approved') as review_count,
        (SELECT AVG(rating) FROM reviews WHERE listing_id = bh.id AND status = 'approved') as avg_rating
        FROM boarding_houses bh
        LEFT JOIN users u ON bh.user_id = u.id
        WHERE bh.status = ?
        ORDER BY bh.created_at DESC
    ");
    $stmt->bind_param("s", $filter);
    $stmt->execute();
    $result = $stmt->get_result();
}

$listings = [];
while ($row = $result->fetch_assoc()) {
    $listings[] = $row;
}

// Count by status
$pending_count = $conn->query("SELECT COUNT(*) as count FROM boarding_houses WHERE status = 'pending'")->fetch_assoc()['count'];
$active_count = $conn->query("SELECT COUNT(*) as count FROM boarding_houses WHERE status IN ('active', 'available')")->fetch_assoc()['count'];
$rejected_count = $conn->query("SELECT COUNT(*) as count FROM boarding_houses WHERE status = 'rejected'")->fetch_assoc()['count'];
$inactive_count = $conn->query("SELECT COUNT(*) as count FROM boarding_houses WHERE status = 'inactive'")->fetch_assoc()['count'];

require_once __DIR__ . '/../includes/header.php';
?>

<style>
body {
    background: #f8f9fa;
}
.listing-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.listing-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}
.table-responsive {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    background: white;
}
.nav-pills .nav-link {
    border-radius: 50px;
    padding: 10px 20px;
    margin-right: 10px;
}
.nav-pills .nav-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.property-img {
    width: 70px;
    height: 70px;
    object-fit: cover;
    border-radius: 8px;
}
.modal-img {
    max-height: 400px;
    object-fit: cover;
    border-radius: 10px;
}
.admin-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 0;
    margin-bottom: 30px;
}
</style>

<!-- Admin Header -->
<div class="admin-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="bi bi-buildings"></i> Manage Listings</h2>
                <p class="mb-0">Review, approve, and manage all property listings</p>
            </div>
            <a href="/board-in/admin/dashboard.php" class="btn btn-light">
                <i class="bi bi-speedometer2"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<div class="container mb-5">
    
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="text-warning"><?php echo $pending_count; ?></h3>
                    <p class="mb-0">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="text-success"><?php echo $active_count; ?></h3>
                    <p class="mb-0">Active</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="text-danger"><?php echo $rejected_count; ?></h3>
                    <p class="mb-0">Rejected</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="text-secondary"><?php echo $inactive_count; ?></h3>
                    <p class="mb-0">Inactive</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === 'all' ? 'active' : ''; ?>" href="?filter=all">
                <i class="bi bi-list"></i> All Listings
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === 'pending' ? 'active' : ''; ?>" href="?filter=pending">
                <i class="bi bi-clock"></i> Pending 
                <?php if ($pending_count > 0): ?>
                    <span class="badge bg-danger"><?php echo $pending_count; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === 'active' ? 'active' : ''; ?>" href="?filter=active">
                <i class="bi bi-check-circle"></i> Active
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === 'rejected' ? 'active' : ''; ?>" href="?filter=rejected">
                <i class="bi bi-x-circle"></i> Rejected
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === 'inactive' ? 'active' : ''; ?>" href="?filter=inactive">
                <i class="bi bi-pause-circle"></i> Inactive
            </a>
        </li>
    </ul>

    <?php if (empty($listings)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No listings found in this category.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">ID</th>
                        <th>Property</th>
                        <th>Landlord</th>
                        <th>Price</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Rooms</th>
                        <th>Views</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listings as $listing): ?>
                        <tr>
                            <td><strong>#<?php echo $listing['id']; ?></strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($listing['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($listing['image']); ?>" 
                                             alt="Property" 
                                             class="property-img me-2">
                                    <?php else: ?>
                                        <div class="property-img me-2 bg-secondary d-flex align-items-center justify-content-center">
                                            <i class="bi bi-house text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo htmlspecialchars($listing['title']); ?></strong><br>
                                        <small class="text-muted">
                                            Posted: <?php echo date('M d, Y', strtotime($listing['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small>
                                    <i class="bi bi-person"></i> <?php echo htmlspecialchars($listing['username'] ?: $listing['full_name']); ?><br>
                                    <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($listing['email']); ?>
                                </small>
                            </td>
                            <td>
                                <strong class="text-primary">₱<?php echo number_format($listing['price'] ?? $listing['monthly_rent'], 2); ?></strong><br>
                                <small class="text-muted">/ month</small>
                            </td>
                            <td><small><?php echo htmlspecialchars(($listing['city'] ?? $listing['location']) . ', ' . ($listing['province'] ?? '')); ?></small></td>
                            <td><span class="badge bg-secondary"><?php echo ucfirst($listing['room_type']); ?></span></td>
                            <td>
                                <small><?php echo $listing['available_rooms']; ?> / <?php echo $listing['total_rooms']; ?></small>
                            </td>
                            <td><small><?php echo number_format($listing['views']); ?></small></td>
                            <td>
                                <?php if ($listing['review_count'] > 0): ?>
                                    <small>
                                        ⭐ <?php echo number_format($listing['avg_rating'], 1); ?>
                                        <br>(<?php echo $listing['review_count']; ?>)
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">No reviews</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($listing['status'] === 'pending'): ?>
                                    <span class="badge bg-warning text-dark px-3 py-2">
                                        <i class="bi bi-clock"></i> Pending
                                    </span>
                                <?php elseif ($listing['status'] === 'active' || $listing['status'] === 'available'): ?>
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="bi bi-check-circle"></i> Active
                                    </span>
                                <?php elseif ($listing['status'] === 'rejected'): ?>
                                    <span class="badge bg-danger px-3 py-2">
                                        <i class="bi bi-x-circle"></i> Rejected
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary px-3 py-2">
                                        <i class="bi bi-pause-circle"></i> Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="/board-in/pages/listing.php?id=<?php echo $listing['id']; ?>" 
                                       target="_blank"
                                       class="btn btn-sm btn-outline-info" 
                                       title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <?php if ($listing['status'] === 'pending'): ?>
                                        <form method="POST" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                            <input type="hidden" name="action" value="active">
                                            <button class="btn btn-sm btn-success" 
                                                    onclick="return confirm('Approve this listing?')"
                                                    title="Approve">
                                                <i class="bi bi-check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                            <input type="hidden" name="action" value="rejected">
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Reject this listing?')"
                                                    title="Reject">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </form>
                                    <?php elseif ($listing['status'] === 'active' || $listing['status'] === 'available'): ?>
                                        <form method="POST" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                            <input type="hidden" name="action" value="rejected">
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('Revoke approval?')"
                                                    title="Reject">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </form>
                                    <?php elseif ($listing['status'] === 'rejected'): ?>
                                        <form method="POST" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                            <input type="hidden" name="action" value="active">
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="return confirm('Approve this listing?')"
                                                    title="Approve">
                                                <i class="bi bi-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="POST" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="return confirm('Delete this listing permanently?')"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>