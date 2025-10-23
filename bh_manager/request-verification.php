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
    $_SESSION['error'] = 'Boarding house not found or you do not have permission.';
    header('Location: /board-in/bh_manager/dashboard.php');
    exit;
}

// Check if already verified or pending
if ($bh['verification_status'] === 'verified') {
    $_SESSION['info'] = 'This boarding house is already verified.';
    header('Location: /board-in/pages/listing.php?id=' . $bh_id);
    exit;
}

if ($bh['verification_status'] === 'pending_verification') {
    $_SESSION['info'] = 'Verification request already submitted. Please wait for admin review.';
    header('Location: /board-in/bh_manager/verification-status.php?id=' . $bh_id);
    exit;
}

// Check rejection count
if ($bh['verification_rejection_count'] >= 3) {
    $_SESSION['error'] = 'This boarding house has been rejected 3 times. Please contact support.';
    header('Location: /board-in/bh_manager/dashboard.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_dir = __DIR__ . '/../uploads/verification-docs/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $required_docs = ['valid_id', 'business_permit', 'proof_ownership', 'barangay_clearance'];
    $uploaded_files = [];
    $errors = [];

    foreach ($required_docs as $doc_type) {
        if (!isset($_FILES[$doc_type]) || $_FILES[$doc_type]['error'] !== UPLOAD_ERR_OK) {
            $errors[] = ucwords(str_replace('_', ' ', $doc_type)) . ' is required.';
            continue;
        }

        $file = $_FILES[$doc_type];
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $errors[] = ucwords(str_replace('_', ' ', $doc_type)) . ' must be JPG, PNG, or PDF.';
            continue;
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            $errors[] = ucwords(str_replace('_', ' ', $doc_type)) . ' must be less than 5MB.';
            continue;
        }

        $filename = uniqid() . '_' . $bh_id . '_' . $doc_type . '.' . $ext;
        $filepath = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $uploaded_files[$doc_type] = '/board-in/uploads/verification-docs/' . $filename;
        } else {
            $errors[] = 'Failed to upload ' . ucwords(str_replace('_', ' ', $doc_type)) . '.';
        }
    }

    if (empty($errors)) {
        // Save documents to database
        $conn->begin_transaction();
        try {
            // Delete old documents if resubmitting
            $stmt = $conn->prepare("DELETE FROM bh_verification_docs WHERE bh_id = ?");
            $stmt->bind_param('i', $bh_id);
            $stmt->execute();

            // Insert new documents
            $stmt = $conn->prepare("INSERT INTO bh_verification_docs (bh_id, doc_type, file_url) VALUES (?, ?, ?)");
            foreach ($uploaded_files as $doc_type => $file_url) {
                $stmt->bind_param('iss', $bh_id, $doc_type, $file_url);
                $stmt->execute();
            }

            // Update boarding house status
            $stmt = $conn->prepare("UPDATE boarding_houses SET verification_status = 'pending_verification', verification_requested_at = NOW(), documents_submitted_at = NOW() WHERE id = ?");
            $stmt->bind_param('i', $bh_id);
            $stmt->execute();

            // Notify admins
            $stmt = $conn->prepare("SELECT id FROM users WHERE role = 'admin'");
            $stmt->execute();
            $admins = $stmt->get_result();
            
            $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
            while ($admin = $admins->fetch_assoc()) {
                $title = 'New Verification Request';
                $message = "Boarding house '{$bh['title']}' has requested verification.";
                $type = 'info';
                $link = '/board-in/admin/verify-bh-queue.php';
                $stmt_notif->bind_param('issss', $admin['id'], $title, $message, $type, $link);
                $stmt_notif->execute();
            }

            $conn->commit();
            $_SESSION['success'] = 'Verification request submitted successfully! Our team will review your documents.';
            header('Location: /board-in/bh_manager/verification-status.php?id=' . $bh_id);
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = 'Failed to submit verification request. Please try again.';
        }
    } else {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}

// Get existing documents if resubmitting
$existing_docs = [];
$stmt = $conn->prepare("SELECT doc_type, file_url FROM bh_verification_docs WHERE bh_id = ?");
$stmt->bind_param('i', $bh_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $existing_docs[$row['doc_type']] = $row['file_url'];
}

// NOW include header AFTER all redirects
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-patch-check"></i> Request Verification</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info">
                        <h5><i class="bi bi-info-circle"></i> Verification Process</h5>
                        <ol class="mb-0">
                            <li>Submit required documents below</li>
                            <li>Admin reviews your documents online</li>
                            <li>Our team schedules an on-site visit</li>
                            <li>Get verified badge after successful inspection</li>
                        </ol>
                    </div>

                    <?php if ($bh['verification_rejection_count'] > 0): ?>
                        <div class="alert alert-warning">
                            <strong>Previous Rejections: <?php echo $bh['verification_rejection_count']; ?>/3</strong><br>
                            <?php if (!empty($bh['verification_notes'])): ?>
                                <small>Reason: <?php echo htmlspecialchars($bh['verification_notes']); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <h5 class="mb-3">Boarding House: <?php echo htmlspecialchars($bh['title']); ?></h5>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-card-heading"></i> Valid ID (Owner/Manager)
                                <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="valid_id" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                            <?php if (isset($existing_docs['valid_id'])): ?>
                                <small class="text-muted">Current: <a href="<?php echo $existing_docs['valid_id']; ?>" target="_blank">View</a></small>
                            <?php endif; ?>
                            <small class="text-muted d-block">Driver's License, Passport, or Government ID</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-file-earmark-text"></i> Business Permit
                                <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="business_permit" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                            <?php if (isset($existing_docs['business_permit'])): ?>
                                <small class="text-muted">Current: <a href="<?php echo $existing_docs['business_permit']; ?>" target="_blank">View</a></small>
                            <?php endif; ?>
                            <small class="text-muted d-block">Valid business permit for rental operations</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-house-check"></i> Proof of Ownership
                                <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="proof_ownership" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                            <?php if (isset($existing_docs['proof_ownership'])): ?>
                                <small class="text-muted">Current: <a href="<?php echo $existing_docs['proof_ownership']; ?>" target="_blank">View</a></small>
                            <?php endif; ?>
                            <small class="text-muted d-block">Land title, deed of sale, or lease contract</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-shield-check"></i> Barangay Clearance
                                <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="barangay_clearance" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                            <?php if (isset($existing_docs['barangay_clearance'])): ?>
                                <small class="text-muted">Current: <a href="<?php echo $existing_docs['barangay_clearance']; ?>" target="_blank">View</a></small>
                            <?php endif; ?>
                            <small class="text-muted d-block">Certificate from local barangay</small>
                        </div>

                        <div class="alert alert-warning">
                            <small><strong>Note:</strong> All files must be clear, readable, and less than 5MB. Accepted formats: JPG, PNG, PDF</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Submit for Verification
                            </button>
                            <a href="/board-in/pages/listing.php?id=<?php echo $bh_id; ?>" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>