<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_role(['landlord']);

$bh_id = intval($_GET['id'] ?? 0);

// Verify ownership
$stmt = $conn->prepare("SELECT * FROM boarding_houses WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $bh_id, $_SESSION['user']['id']);
$stmt->execute();
$bh = $stmt->get_result()->fetch_assoc();

if (!$bh) {
    $_SESSION['error'] = 'Boarding house not found.';
    header('Location: /board-in/bh_manager/dashboard.php');
    exit;
}

// ... rest of the PHP logic ...

// MOVE THIS TO HERE - After all redirects
require_once __DIR__ . '/../includes/header.php';



// Get documents

$docs = [];
$stmt = $conn->prepare("SELECT * FROM bh_verification_docs WHERE bh_id = ? ORDER BY uploaded_at DESC");
$stmt->bind_param('i', $bh_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $docs[$row['doc_type']] = $row;
}

// Get visit info
$visit = null;
$stmt = $conn->prepare("SELECT * FROM bh_verification_visits WHERE bh_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param('i', $bh_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $visit = $result->fetch_assoc();
}

$status_config = [
    'unverified' => ['icon' => 'bi-shield-x', 'color' => 'secondary', 'text' => 'Not Verified'],
    'pending_verification' => ['icon' => 'bi-clock-history', 'color' => 'warning', 'text' => 'Pending Review'],
    'verified' => ['icon' => 'bi-patch-check-fill', 'color' => 'success', 'text' => 'Verified'],
    'rejected' => ['icon' => 'bi-x-circle', 'color' => 'danger', 'text' => 'Rejected']
];

$current_status = $status_config[$bh['verification_status']] ?? $status_config['unverified'];
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header bg-<?php echo $current_status['color']; ?> text-white">
                    <h4 class="mb-0">
                        <i class="bi <?php echo $current_status['icon']; ?>"></i>
                        Verification Status: <?php echo $current_status['text']; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <h5><?php echo htmlspecialchars($bh['title']); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($bh['address']); ?></p>

                    <hr>

                    <!-- Progress Timeline -->
                    <div class="verification-timeline">
                        <div class="timeline-item <?php echo !empty($docs) ? 'completed' : 'pending'; ?>">
                            <div class="timeline-marker">
                                <i class="bi bi-file-earmark-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Documents Submitted</h6>
                                <?php if (!empty($docs)): ?>
                                    <small class="text-success">
                                        ✓ Completed on <?php echo date('M d, Y', strtotime($bh['documents_submitted_at'])); ?>
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">Waiting for submission</small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="timeline-item <?php echo $bh['verification_status'] === 'pending_verification' || $bh['verification_status'] === 'verified' ? 'completed' : 'pending'; ?>">
                            <div class="timeline-marker">
                                <i class="bi bi-person-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Admin Review</h6>
                                <?php if ($bh['verification_status'] === 'pending_verification'): ?>
                                    <small class="text-warning">⏳ Under review</small>
                                <?php elseif ($bh['verification_status'] === 'verified'): ?>
                                    <small class="text-success">✓ Approved</small>
                                <?php elseif ($bh['verification_status'] === 'rejected'): ?>
                                    <small class="text-danger">✗ Rejected</small>
                                <?php else: ?>
                                    <small class="text-muted">Waiting</small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="timeline-item <?php echo $visit && $visit['status'] === 'scheduled' ? 'active' : ($visit && $visit['status'] === 'completed' ? 'completed' : 'pending'); ?>">
                            <div class="timeline-marker">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>On-Site Visit</h6>
                                <?php if ($visit && $visit['status'] === 'scheduled'): ?>
                                    <small class="text-info">
                                        📅 Scheduled for <?php echo date('M d, Y', strtotime($visit['scheduled_date'])); ?>
                                    </small>
                                <?php elseif ($visit && $visit['status'] === 'completed'): ?>
                                    <small class="text-success">
                                        ✓ Completed on <?php echo date('M d, Y', strtotime($visit['completed_date'])); ?>
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">Not scheduled yet</small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="timeline-item <?php echo $bh['verification_status'] === 'verified' ? 'completed' : 'pending'; ?>">
                            <div class="timeline-marker">
                                <i class="bi bi-patch-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Verified Badge</h6>
                                <?php if ($bh['verification_status'] === 'verified'): ?>
                                    <small class="text-success">
                                        ✓ Verified on <?php echo date('M d, Y', strtotime($bh['verification_completed_at'])); ?>
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">Pending completion</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Rejection Notice -->
                    <?php if ($bh['verification_status'] === 'rejected'): ?>
                        <div class="alert alert-danger mt-4">
                            <h6><i class="bi bi-exclamation-triangle"></i> Verification Rejected</h6>
                            <p class="mb-1"><strong>Reason:</strong></p>
                            <p><?php echo nl2br(htmlspecialchars($bh['verification_notes'] ?? 'No reason provided')); ?></p>
                            <p class="mb-0"><small>Rejection Count: <?php echo $bh['verification_rejection_count']; ?>/3</small></p>
                        </div>

                        <?php if ($bh['verification_rejection_count'] < 3): ?>
                            <a href="/board-in/bh_manager/request-verification.php?id=<?php echo $bh_id; ?>" class="btn btn-warning">
                                <i class="bi bi-arrow-clockwise"></i> Resubmit Documents
                            </a>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <strong>Maximum rejections reached.</strong> Please contact support at support@board-in.com
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Submitted Documents -->
                    <?php if (!empty($docs)): ?>
                        <hr>
                        <h6>Submitted Documents</h6>
                        <div class="row">
                            <?php
                            $doc_labels = [
                                'valid_id' => 'Valid ID',
                                'business_permit' => 'Business Permit',
                                'proof_ownership' => 'Proof of Ownership',
                                'barangay_clearance' => 'Barangay Clearance'
                            ];
                            foreach ($docs as $type => $doc):
                            ?>
                                <div class="col-md-6 mb-2">
                                    <div class="card">
                                        <div class="card-body p-2">
                                            <small class="text-muted d-block"><?php echo $doc_labels[$type] ?? $type; ?></small>
                                            <a href="<?php echo htmlspecialchars($doc['file_url']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <small class="text-muted">Uploaded: <?php echo date('M d, Y', strtotime($doc['uploaded_at'])); ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <a href="/board-in/pages/listing.php?id=<?php echo $bh_id; ?>" class="btn btn-primary">
                            <i class="bi bi-house"></i> View Listing
                        </a>
                        <a href="/board-in/bh_manager/dashboard.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.verification-timeline {
    position: relative;
    padding-left: 40px;
}

.verification-timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    padding-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -40px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: #6c757d;
}

.timeline-item.completed .timeline-marker {
    background: #28a745;
    border-color: #28a745;
    color: white;
}

.timeline-item.active .timeline-marker {
    background: #ffc107;
    border-color: #ffc107;
    color: white;
}

.timeline-content h6 {
    margin-bottom: 4px;
    font-weight: 600;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>