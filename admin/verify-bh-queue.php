<?php
require_once __DIR__ . '/../config/session.php';
require_role(['admin']);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

// Get pending verifications
$stmt = $conn->prepare("
    SELECT bh.*, u.email, u.full_name, u.contact_number,
           (SELECT COUNT(*) FROM bh_verification_docs WHERE bh_id = bh.id) as doc_count
    FROM boarding_houses bh 
    JOIN users u ON u.id = bh.user_id 
    WHERE bh.verification_status = 'pending_verification' 
    ORDER BY bh.verification_requested_at ASC
");
$stmt->execute();
$pending = $stmt->get_result();
?>

<div class="container mt-4">
    <h2><i class="bi bi-shield-check"></i> Boarding House Verification Queue</h2>
    <p class="text-muted">Review verification requests and schedule on-site visits</p>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($pending->num_rows === 0): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No pending verification requests.
        </div>
    <?php else: ?>
        <?php while ($bh = $pending->fetch_assoc()): ?>
            <?php
            // Get documents
            $stmt_docs = $conn->prepare("SELECT * FROM bh_verification_docs WHERE bh_id = ?");
            $stmt_docs->bind_param('i', $bh['id']);
            $stmt_docs->execute();
            $docs_result = $stmt_docs->get_result();
            $docs = [];
            while ($doc = $docs_result->fetch_assoc()) {
                $docs[$doc['doc_type']] = $doc['file_url'];
            }
            ?>

            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-warning bg-opacity-10">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="bi bi-house-door"></i> <?php echo htmlspecialchars($bh['title']); ?>
                            </h5>
                            <small class="text-muted">
                                Requested: <?php echo date('M d, Y h:i A', strtotime($bh['verification_requested_at'])); ?>
                            </small>
                        </div>
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-clock-history"></i> Pending Review
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Property Details</h6>
                            <p class="mb-1"><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($bh['address'])); ?></p>
                            <p class="mb-1"><strong>Price:</strong> ₱<?php echo number_format($bh['monthly_rent'], 2); ?>/month</p>
                            <p class="mb-1"><strong>Rooms:</strong> <?php echo $bh['available_rooms']; ?>/<?php echo $bh['total_rooms']; ?> available</p>
                            
                            <h6 class="text-muted mt-3">Landlord Information</h6>
                            <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($bh['full_name']); ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($bh['email']); ?></p>
                            <p class="mb-1"><strong>Contact:</strong> <?php echo htmlspecialchars($bh['contact_number'] ?? 'N/A'); ?></p>
                            
                            <?php if ($bh['verification_rejection_count'] > 0): ?>
                                <div class="alert alert-warning mt-2">
                                    <small><strong>Previous Rejections:</strong> <?php echo $bh['verification_rejection_count']; ?>/3</small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted">Submitted Documents (<?php echo count($docs); ?>/4)</h6>
                            <div class="list-group">
                                <?php
                                $doc_labels = [
                                    'valid_id' => 'Valid ID',
                                    'business_permit' => 'Business Permit',
                                    'proof_ownership' => 'Proof of Ownership',
                                    'barangay_clearance' => 'Barangay Clearance'
                                ];
                                foreach ($doc_labels as $key => $label):
                                ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            <?php if (isset($docs[$key])): ?>
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            <?php else: ?>
                                                <i class="bi bi-x-circle-fill text-danger"></i>
                                            <?php endif; ?>
                                            <?php echo $label; ?>
                                        </span>
                                        <?php if (isset($docs[$key])): ?>
                                            <a href="<?php echo htmlspecialchars($docs[$key]); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-danger">Missing</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="/board-in/pages/listing.php?id=<?php echo $bh['id']; ?>" target="_blank" class="btn btn-info">
                            <i class="bi bi-eye"></i> View Full Listing
                        </a>

                        <!-- Schedule Visit Button -->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleModal<?php echo $bh['id']; ?>">
                            <i class="bi bi-calendar-check"></i> Schedule Visit
                        </button>

                        <!-- Reject Button -->
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $bh['id']; ?>">
                            <i class="bi bi-x-circle"></i> Reject
                        </button>
                    </div>
                </div>
            </div>

            <!-- Schedule Visit Modal -->
            <div class="modal fade" id="scheduleModal<?php echo $bh['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="/board-in/backend/process-bh-verification.php">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title"><i class="bi bi-calendar-check"></i> Schedule On-Site Visit</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="schedule_visit">
                                <input type="hidden" name="bh_id" value="<?php echo $bh['id']; ?>">

                                <div class="mb-3">
                                    <label class="form-label">Visit Date <span class="text-danger">*</span></label>
                                    <input type="date" name="scheduled_date" class="form-control" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Assigned Verifier <span class="text-danger">*</span></label>
                                    <input type="text" name="verified_by" class="form-control" 
                                           placeholder="e.g., John Doe" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Visit Notes (Optional)</label>
                                    <textarea name="visit_notes" class="form-control" rows="3" 
                                              placeholder="Special instructions or notes..."></textarea>
                                </div>

                                <div class="alert alert-info">
                                    <small><i class="bi bi-info-circle"></i> The landlord will be notified about the scheduled visit.</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Schedule Visit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Reject Modal -->
            <div class="modal fade" id="rejectModal<?php echo $bh['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="/board-in/backend/process-bh-verification.php">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title"><i class="bi bi-x-circle"></i> Reject Verification Request</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="bh_id" value="<?php echo $bh['id']; ?>">

                                <div class="alert alert-warning">
                                    <strong>Current Rejections:</strong> <?php echo $bh['verification_rejection_count']; ?>/3
                                    <?php if ($bh['verification_rejection_count'] >= 2): ?>
                                        <br><small class="text-danger">⚠️ This is the last rejection allowed!</small>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                                    <textarea name="rejection_notes" class="form-control" rows="4" required
                                              placeholder="Explain why this verification is being rejected..."></textarea>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="blockUser<?php echo $bh['id']; ?>" name="block_user" value="1">
                                    <label class="form-check-label text-danger" for="blockUser<?php echo $bh['id']; ?>">
                                        <strong>Block this landlord from future verifications</strong>
                                        <br><small>Use this for fraudulent or spam requests</small>
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-x-circle"></i> Reject Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <!-- Scheduled Visits Section -->
    <?php
    $stmt_visits = $conn->prepare("
        SELECT bv.*, bh.title, bh.address, u.full_name 
        FROM bh_verification_visits bv
        JOIN boarding_houses bh ON bh.id = bv.bh_id
        JOIN users u ON u.id = bh.user_id
        WHERE bv.status = 'scheduled'
        ORDER BY bv.scheduled_date ASC
    ");
    $stmt_visits->execute();
    $visits = $stmt_visits->get_result();
    ?>

    <?php if ($visits->num_rows > 0): ?>
        <h3 class="mt-5"><i class="bi bi-calendar-event"></i> Scheduled Visits</h3>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Boarding House</th>
                        <th>Landlord</th>
                        <th>Verifier</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($visit = $visits->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($visit['scheduled_date'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($visit['title']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($visit['address']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($visit['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($visit['verified_by']); ?></td>
                            <td>
                                <a href="/board-in/admin/complete-visit.php?visit_id=<?php echo $visit['id']; ?>" 
                                   class="btn btn-sm btn-success">
                                    <i class="bi bi-check-circle"></i> Complete Visit
                                </a>
                                <a href="/board-in/pages/listing.php?id=<?php echo $visit['bh_id']; ?>" 
                                   target="_blank" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>