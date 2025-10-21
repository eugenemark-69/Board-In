<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

// ✅ Admin-only access check (fixed to use user_type)
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
    flash('error', 'Access denied.');
    header('Location: /board-in/index.php');
    exit;
}

$pdo = getDB();

// ✅ Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listing_id = $_POST['listing_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($listing_id && in_array($action, ['approved', 'rejected'])) {
        $stmt = $pdo->prepare("UPDATE listings SET status = ? WHERE id = ?");
        $stmt->execute([$action, $listing_id]);

        flash('success', "Listing has been $action successfully.");
        header('Location: manage-listings.php');
        exit;
    }
}

// ✅ Get filter status
$filter = $_GET['filter'] ?? 'all';
$allowed_filters = ['all', 'pending', 'approved', 'rejected'];
if (!in_array($filter, $allowed_filters)) {
    $filter = 'all';
}

// ✅ Fetch listings based on filter
if ($filter === 'all') {
    $stmt = $pdo->query("
        SELECT listings.*, users.username, users.email 
        FROM listings 
        JOIN users ON listings.user_id = users.id 
        ORDER BY 
            CASE WHEN listings.status = 'pending' THEN 0 ELSE 1 END,
            listings.created_at DESC
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT listings.*, users.username, users.email 
        FROM listings 
        JOIN users ON listings.user_id = users.id 
        WHERE listings.status = ?
        ORDER BY listings.created_at DESC
    ");
    $stmt->execute([$filter]);
}
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count by status
$pending_count = $pdo->query("SELECT COUNT(*) FROM listings WHERE status = 'pending'")->fetchColumn();
?>

<style>
.listing-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.listing-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}
.dti-preview {
    max-width: 100%;
    max-height: 500px;
    object-fit: contain;
}
.table-responsive {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}
</style>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-buildings"></i> Manage Listings</h2>
        <a href="/board-in/admin/dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
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
            <a class="nav-link <?php echo $filter === 'approved' ? 'active' : ''; ?>" href="?filter=approved">
                <i class="bi bi-check-circle"></i> Approved
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === 'rejected' ? 'active' : ''; ?>" href="?filter=rejected">
                <i class="bi bi-x-circle"></i> Rejected
            </a>
        </li>
    </ul>

    <?php if (empty($listings)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No listings found in this category.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="50">ID</th>
                        <th>Property</th>
                        <th>Landlord</th>
                        <th>Price</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>DTI</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listings as $listing): ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars($listing['id']) ?></strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($listing['image'])): ?>
                                        <img src="/board-in/<?= htmlspecialchars($listing['image']) ?>" 
                                             alt="Property" 
                                             width="70" 
                                             height="70"
                                             style="object-fit: cover; border-radius: 8px;"
                                             class="me-2">
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= htmlspecialchars($listing['title']) ?></strong><br>
                                        <small class="text-muted">
                                            Posted: <?= date('M d, Y', strtotime($listing['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small>
                                    <i class="bi bi-person"></i> <?= htmlspecialchars($listing['username']) ?><br>
                                    <i class="bi bi-envelope"></i> <?= htmlspecialchars($listing['email']) ?>
                                </small>
                            </td>
                            <td>
                                <strong class="text-primary">₱<?= number_format($listing['price'], 2) ?></strong><br>
                                <small class="text-muted">/ month</small>
                            </td>
                            <td><small><?= htmlspecialchars($listing['location']) ?></small></td>
                            <td>
                                <?php if ($listing['status'] === 'pending'): ?>
                                    <span class="badge bg-warning text-dark px-3 py-2">
                                        <i class="bi bi-clock"></i> Pending
                                    </span>
                                <?php elseif ($listing['status'] === 'approved'): ?>
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="bi bi-check-circle"></i> Approved
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger px-3 py-2">
                                        <i class="bi bi-x-circle"></i> Rejected
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($listing['dti_certificate'])): ?>
                                    <?php
                                    // Fix the path - remove any leading slashes or /board-in/
                                    $dti_cert = ltrim($listing['dti_certificate'], '/');
                                    $dti_cert = str_replace('board-in/', '', $dti_cert);
                                    $dti_path = '/board-in/' . $dti_cert;
                                    $full_path = $_SERVER['DOCUMENT_ROOT'] . $dti_path;
                                    $file_exists = file_exists($full_path);
                                    ?>
                                    
                                    <button class="btn btn-sm <?= $file_exists ? 'btn-outline-primary' : 'btn-outline-danger' ?>" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#dtiModal<?= $listing['id'] ?>">
                                        <i class="bi bi-file-earmark-text"></i> 
                                        <?= $file_exists ? 'View' : 'Missing' ?>
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted"><em>No DTI</em></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detailsModal<?= $listing['id'] ?>"
                                            title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    
                                    <?php if ($listing['status'] === 'pending'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
                                            <input type="hidden" name="action" value="approved">
                                            <button class="btn btn-sm btn-success" 
                                                    onclick="return confirm('Approve this listing?')"
                                                    title="Approve">
                                                <i class="bi bi-check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
                                            <input type="hidden" name="action" value="rejected">
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Reject this listing?')"
                                                    title="Reject">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </form>
                                    <?php elseif ($listing['status'] === 'approved'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
                                            <input type="hidden" name="action" value="rejected">
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('Revoke approval?')"
                                                    title="Reject">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </form>
                                    <?php elseif ($listing['status'] === 'rejected'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
                                            <input type="hidden" name="action" value="approved">
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="return confirm('Approve this listing?')"
                                                    title="Approve">
                                                <i class="bi bi-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>

                        <!-- DTI Modal -->
                        <div class="modal fade" id="dtiModal<?= $listing['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="bi bi-file-earmark-text"></i> DTI Certificate - <?= htmlspecialchars($listing['title']) ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center bg-light">
                                        <?php 
                                        // Fix the path
                                        $dti_cert = ltrim($listing['dti_certificate'], '/');
                                        $dti_cert = str_replace('board-in/', '', $dti_cert);
                                        $dti_path = '/board-in/' . htmlspecialchars($dti_cert);
                                        $file_ext = strtolower(pathinfo($dti_cert, PATHINFO_EXTENSION));
                                        $full_path = $_SERVER['DOCUMENT_ROOT'] . $dti_path;
                                        $file_exists = file_exists($full_path);
                                        ?>
                                        
                                        <?php if (!$file_exists): ?>
                                            <div class="alert alert-danger">
                                                <i class="bi bi-exclamation-triangle"></i> <strong>File not found!</strong><br>
                                                <small class="mt-2 d-block">Expected path: <?= htmlspecialchars($dti_path) ?></small>
                                                <small class="d-block">Database value: <?= htmlspecialchars($listing['dti_certificate']) ?></small>
                                                <small class="d-block">Server path: <?= htmlspecialchars($full_path) ?></small>
                                            </div>
                                        <?php elseif ($file_ext === 'pdf'): ?>
                                            <div class="py-5">
                                                <i class="bi bi-file-pdf text-danger" style="font-size: 5rem;"></i>
                                                <h4 class="mt-3">PDF Document</h4>
                                                <p class="text-muted">Click below to open the PDF in a new tab</p>
                                                <a href="<?= $dti_path ?>" target="_blank" class="btn btn-primary btn-lg">
                                                    <i class="bi bi-box-arrow-up-right"></i> Open PDF
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <img src="<?= $dti_path ?>" 
                                                 class="dti-preview img-fluid rounded shadow" 
                                                 alt="DTI Certificate"
                                                 onerror="this.parentElement.innerHTML='<div class=\'alert alert-danger\'><i class=\'bi bi-exclamation-triangle\'></i> Image failed to load</div>'">
                                            <div class="mt-3">
                                                <a href="<?= $dti_path ?>" target="_blank" class="btn btn-primary">
                                                    <i class="bi bi-arrows-fullscreen"></i> View Full Size
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Details Modal -->
                        <div class="modal fade" id="detailsModal<?= $listing['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="bi bi-info-circle"></i> <?= htmlspecialchars($listing['title']) ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php if (!empty($listing['image'])): ?>
                                            <img src="/board-in/<?= htmlspecialchars($listing['image']) ?>" 
                                                 class="img-fluid mb-3 rounded shadow" 
                                                 alt="Property">
                                        <?php endif; ?>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong><i class="bi bi-cash"></i> Price:</strong> ₱<?= number_format($listing['price']) ?> / month</p>
                                                <p><strong><i class="bi bi-geo-alt"></i> Location:</strong> <?= htmlspecialchars($listing['location']) ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong><i class="bi bi-person"></i> Landlord:</strong> <?= htmlspecialchars($listing['username']) ?></p>
                                                <p><strong><i class="bi bi-envelope"></i> Email:</strong> <?= htmlspecialchars($listing['email']) ?></p>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        
                                        <p><strong><i class="bi bi-list-stars"></i> Amenities:</strong><br>
                                        <?= htmlspecialchars($listing['amenities'] ?: 'None specified') ?></p>
                                        
                                        <p><strong><i class="bi bi-card-text"></i> Description:</strong><br>
                                        <?= nl2br(htmlspecialchars($listing['description'] ?: 'No description provided')) ?></p>
                                        
                                        <hr>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i> Submitted: <?= date('F d, Y h:i A', strtotime($listing['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>