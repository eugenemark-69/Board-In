<?php
require_once __DIR__ . '/../config/session.php';
require_role(['admin']);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

$visit_id = intval($_GET['visit_id'] ?? 0);

// Get visit details
$stmt = $conn->prepare("
    SELECT bv.*, bh.title, bh.address, bh.user_id, u.full_name as landlord_name
    FROM bh_verification_visits bv
    JOIN boarding_houses bh ON bh.id = bv.bh_id
    JOIN users u ON u.id = bh.user_id
    WHERE bv.id = ? AND bv.status = 'scheduled'
");
$stmt->bind_param('i', $visit_id);
$stmt->execute();
$visit = $stmt->get_result()->fetch_assoc();

if (!$visit) {
    $_SESSION['error'] = 'Visit not found or already completed.';
    header('Location: /board-in/admin/verify-bh-queue.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photos_match = isset($_POST['photos_match']) ? 1 : 0;
    $amenities_match = isset($_POST['amenities_match']) ? 1 : 0;
    $address_confirmed = isset($_POST['address_confirmed']) ? 1 : 0;
    $visit_notes = trim($_POST['visit_notes'] ?? '');
    $verification_decision = $_POST['verification_decision']; // 'approve' or 'reject'

    $conn->begin_transaction();
    try {
        // Update visit record
        $stmt = $conn->prepare("
            UPDATE bh_verification_visits 
            SET status = 'completed', 
                completed_date = CURDATE(),
                photos_match = ?,
                amenities_match = ?,
                address_confirmed = ?,
                visit_notes = ?
            WHERE id = ?
        ");
        $stmt->bind_param('iiisi', $photos_match, $amenities_match, $address_confirmed, $visit_notes, $visit_id);
        $stmt->execute();

        if ($verification_decision === 'approve') {
            // Approve verification
            $stmt = $conn->prepare("
                UPDATE boarding_houses 
                SET verification_status = 'verified',
                    verification_completed_at = NOW(),
                    last_verified_at = NOW(),
                    verified_by_admin_id = ?
                WHERE id = ?
            ");
            $stmt->bind_param('ii', $_SESSION['user']['id'], $visit['bh_id']);
            $stmt->execute();

            // Notify landlord - APPROVED
            $title = 'Boarding House Verified! ✓';
            $message = "Great news! Your property '{$visit['title']}' has been successfully verified. It now displays a verified badge.";
            $type = 'success';
            $link = '/board-in/pages/listing.php?id=' . $visit['bh_id'];

            $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('issss', $visit['user_id'], $title, $message, $type, $link);
            $stmt->execute();

            $_SESSION['success'] = 'Boarding house verified successfully!';

        } else {
            // Reject verification
            $rejection_reason = trim($_POST['rejection_reason'] ?? '');
            
            $stmt = $conn->prepare("
                UPDATE boarding_houses 
                SET verification_status = 'rejected',
                    verification_notes = ?,
                    verification_rejection_count = verification_rejection_count + 1
                WHERE id = ?
            ");
            $stmt->bind_param('si', $rejection_reason, $visit['bh_id']);
            $stmt->execute();

            // Notify landlord - REJECTED
            $title = 'Verification Failed';
            $message = "Unfortunately, your property '{$visit['title']}' did not pass verification. Reason: {$rejection_reason}";
            $type = 'warning';
            $link = '/board-in/bh_manager/verification-status.php?id=' . $visit['bh_id'];

            $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('issss', $visit['user_id'], $title, $message, $type, $link);
            $stmt->execute();

            $_SESSION['success'] = 'Visit completed. Verification rejected with feedback sent to landlord.';
        }

        $conn->commit();
        header('Location: /board-in/admin/verify-bh-queue.php');
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Error completing visit: ' . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-clipboard-check"></i> Complete Verification Visit</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <h5><?php echo htmlspecialchars($visit['title']); ?></h5>
                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($visit['address'])); ?></p>
                    <p><strong>Landlord:</strong> <?php echo htmlspecialchars($visit['landlord_name']); ?></p>
                    <p><strong>Scheduled Date:</strong> <?php echo date('M d, Y', strtotime($visit['scheduled_date'])); ?></p>
                    <p><strong>Verifier:</strong> <?php echo htmlspecialchars($visit['verified_by']); ?></p>

                    <hr>

                    <form method="POST">
                        <h6 class="mb-3">Verification Checklist</h6>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="photos_match" id="photos_match" value="1">
                            <label class="form-check-label" for="photos_match">
                                <strong>Photos match actual property</strong>
                                <br><small class="text-muted">Verify that listing photos accurately represent the property</small>
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="amenities_match" id="amenities_match" value="1">
                            <label class="form-check-label" for="amenities_match">
                                <strong>Amenities confirmed</strong>
                                <br><small class="text-muted">All listed amenities are present and functional</small>
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="address_confirmed" id="address_confirmed" value="1">
                            <label class="form-check-label" for="address_confirmed">
                                <strong>Address verified</strong>
                                <br><small class="text-muted">Property exists at the stated address</small>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Visit Notes</label>
                            <textarea name="visit_notes" class="form-control" rows="4" 
                                      placeholder="Additional observations, concerns, or notes from the visit..."></textarea>
                        </div>

                        <hr>

                        <h6 class="mb-3">Verification Decision</h6>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="verification_decision" 
                                       id="approve" value="approve" required>
                                <label class="form-check-label text-success fw-bold" for="approve">
                                    <i class="bi bi-check-circle"></i> Approve Verification
                                    <br><small class="text-muted">Property meets all standards</small>
                                </label>
                            </div>

                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="verification_decision" 
                                       id="reject" value="reject">
                                <label class="form-check-label text-danger fw-bold" for="reject">
                                    <i class="bi bi-x-circle"></i> Reject Verification
                                    <br><small class="text-muted">Property does not meet standards</small>
                                </label>
                            </div>
                        </div>

                        <div id="rejection_reason_box" style="display: none;">
                            <div class="alert alert-warning">
                                <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                <textarea name="rejection_reason" class="form-control" rows="3"
                                          placeholder="Explain why the verification failed (visible to landlord)..."></textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Complete Visit
                            </button>
                            <a href="/board-in/admin/verify-bh-queue.php" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('input[name="verification_decision"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const rejectionBox = document.getElementById('rejection_reason_box');
        const rejectionTextarea = document.querySelector('textarea[name="rejection_reason"]');
        
        if (this.value === 'reject') {
            rejectionBox.style.display = 'block';
            rejectionTextarea.required = true;
        } else {
            rejectionBox.style.display = 'none';
            rejectionTextarea.required = false;
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>