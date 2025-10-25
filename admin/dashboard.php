<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role(['admin']);
require_once __DIR__ . '/../includes/header.php';

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (!csrf_check($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid security token. Please try again.';
        header('Location: /board-in/admin/dashboard.php');
        exit;
    }

    $listing_id = $_POST['listing_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($listing_id && in_array($action, ['active', 'rejected'])) {
        $stmt = $conn->prepare("UPDATE boarding_houses SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $action, $listing_id);
        
        if ($stmt->execute()) {
            // Get listing details for notification
            $stmt2 = $conn->prepare("SELECT user_id, title FROM boarding_houses WHERE id = ?");
            $stmt2->bind_param('i', $listing_id);
            $stmt2->execute();
            $result = $stmt2->get_result();
            $listing = $result->fetch_assoc();
            
            if ($listing) {
                // Send notification to landlord
                $notif_title = "Listing " . ucfirst($action);
                $notif_message = "Your listing '{$listing['title']}' has been " . ($action === 'active' ? 'approved' : 'rejected') . " by an administrator.";
                $notif_type = $action === 'active' ? 'success' : 'warning';
                $notif_link = '/board-in/pages/listing.php?id=' . $listing_id;
                
                $stmt3 = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
                $stmt3->bind_param('issss', $listing['user_id'], $notif_title, $notif_message, $notif_type, $notif_link);
                $stmt3->execute();
            }
            
            $_SESSION['success'] = "Listing has been " . ($action === 'active' ? 'approved' : 'rejected') . " successfully.";
        } else {
            $_SESSION['error'] = 'Error updating listing: ' . $conn->error;
        }
        
        
        exit;
    }
}

// Fetch pending listings from boarding_houses table
$stmt = $conn->prepare("
    SELECT bh.*, u.username, u.full_name, u.email
    FROM boarding_houses bh
    JOIN users u ON bh.user_id = u.id
    WHERE bh.status = 'pending'
    ORDER BY bh.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
$listings = [];
while ($row = $result->fetch_assoc()) {
    $listings[] = $row;
}

// Get statistics
$stmt_stats = $conn->query("
    SELECT 
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
        COUNT(CASE WHEN status = 'active' OR status = 'available' THEN 1 END) as active_count,
        COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count,
        COUNT(*) as total_count
    FROM boarding_houses
");
$stats = $stmt_stats->fetch_assoc();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
    </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-speedometer2"></i> Admin Dashboard</h2>
            <p class="text-muted mb-0">Manage pending listing approvals</p>
        </div>
        <div class="btn-group">
            <a href="/board-in/admin/verify-bh-queue.php" class="btn btn-warning">
                <i class="bi bi-shield-check"></i> Verification Queue
                <?php
                // Count pending verifications
                $count_stmt = $conn->query("SELECT COUNT(*) as count FROM boarding_houses WHERE verification_status = 'pending_verification'");
                $count = $count_stmt->fetch_assoc()['count'];
                if ($count > 0):
                ?>
                    <span class="badge bg-danger"><?php echo $count; ?></span>
                <?php endif; ?>
            </a>
            <a href="/board-in/admin/manage-listings.php" class="btn btn-primary">
                <i class="bi bi-list-check"></i> Manage All Listings
            </a>
        </div>
    </div>

    <!-- Display flash messages -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['pending_count']; ?></h3>
                    <p class="text-muted mb-0">Pending Approval</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['active_count']; ?></h3>
                    <p class="text-muted mb-0">Active Listings</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 text-danger mb-2">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['rejected_count']; ?></h3>
                    <p class="text-muted mb-0">Rejected</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2">
                        <i class="bi bi-houses"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $stats['total_count']; ?></h3>
                    <p class="text-muted mb-0">Total Listings</p>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mb-3">
        <i class="bi bi-clipboard-check"></i> Pending Listings 
        <?php if ($stats['pending_count'] > 0): ?>
            <span class="badge bg-warning text-dark"><?php echo $stats['pending_count']; ?></span>
        <?php endif; ?>
    </h4>

    <?php if (empty($listings)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No pending listings. All submissions have been reviewed.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="60">ID</th>
                        <th>Title</th>
                        <th>Landlord</th>
                        <th>Price</th>
                        <th>Location</th>
                        <th width="100">Image</th>
                        <th width="120">Submitted</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listings as $listing): ?>
                        <tr>
                            <td class="text-center">
                                <strong>#<?php echo $listing['id']; ?></strong>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($listing['title']); ?></strong>
                                <?php if (!empty($listing['description'])): ?>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars(substr($listing['description'], 0, 60)); ?>...
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($listing['full_name'] ?? $listing['username']); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($listing['email']); ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <strong class="text-primary">₱<?php echo number_format($listing['price'] ?? $listing['monthly_rent'], 2); ?></strong>
                                <br>
                                <small class="text-muted">per month</small>
                            </td>
                            <td>
                                <small>
                                    <?php echo htmlspecialchars($listing['city'] ?? $listing['location']); ?>
                                    <?php if (!empty($listing['province'])): ?>
                                        <br><?php echo htmlspecialchars($listing['province']); ?>
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>
                                <?php if (!empty($listing['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($listing['image']); ?>" 
                                         alt="Listing" 
                                         class="img-thumbnail"
                                         style="width: 80px; height: 60px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="text-center text-muted">
                                        <i class="bi bi-image fs-3"></i>
                                        <br>
                                        <small>No image</small>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small>
                                    <?php echo date('M d, Y', strtotime($listing['created_at'])); ?>
                                    <br>
                                    <?php echo date('h:i A', strtotime($listing['created_at'])); ?>
                                </small>
                            </td>
                            <td>
                                <div class="btn-group-vertical w-100" role="group">
                                    <a href="/board-in/pages/listing.php?id=<?php echo $listing['id']; ?>" 
                                       target="_blank"
                                       class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    
                                    <form method="POST" class="mb-1">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                        <input type="hidden" name="action" value="active">
                                        <button class="btn btn-sm btn-success w-100" 
                                                onclick="return confirm('Approve this listing? It will become visible to students.')">
                                            <i class="bi bi-check-circle"></i> Approve
                                        </button>
                                    </form>
                                    
                                    <form method="POST">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                        <input type="hidden" name="action" value="rejected">
                                        <button class="btn btn-sm btn-danger w-100" 
                                                onclick="return confirm('Reject this listing? The landlord will be notified.')">
                                            <i class="bi bi-x-circle"></i> Reject
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