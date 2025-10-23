<?php
require_once __DIR__ . '/../config/session.php';
require_role(['admin']);
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /board-in/admin/verify-bh-queue.php');
    exit;
}

$action = $_POST['action'] ?? '';
$bh_id = intval($_POST['bh_id'] ?? 0);

if (!$bh_id) {
    $_SESSION['error'] = 'Invalid boarding house ID.';
    header('Location: /board-in/admin/verify-bh-queue.php');
    exit;
}

// Get boarding house details
$stmt = $conn->prepare("SELECT user_id, title FROM boarding_houses WHERE id = ?");
$stmt->bind_param('i', $bh_id);
$stmt->execute();
$bh = $stmt->get_result()->fetch_assoc();

if (!$bh) {
    $_SESSION['error'] = 'Boarding house not found.';
    header('Location: /board-in/admin/verify-bh-queue.php');
    exit;
}

$conn->begin_transaction();

try {
    if ($action === 'schedule_visit') {
        $scheduled_date = $_POST['scheduled_date'] ?? '';
        $verified_by = trim($_POST['verified_by'] ?? '');
        $visit_notes = trim($_POST['visit_notes'] ?? '');

        if (empty($scheduled_date) || empty($verified_by)) {
            throw new Exception('Scheduled date and verifier name are required.');
        }

        // Insert visit record
        $stmt = $conn->prepare("
            INSERT INTO bh_verification_visits (bh_id, scheduled_date, verified_by, visit_notes, status)
            VALUES (?, ?, ?, ?, 'scheduled')
        ");
        $stmt->bind_param('isss', $bh_id, $scheduled_date, $verified_by, $visit_notes);
        $stmt->execute();

        // Notify landlord
        $title = 'Verification Visit Scheduled';
        $message = "An on-site visit for '{$bh['title']}' has been scheduled for " . date('F d, Y', strtotime($scheduled_date)) . ". Our team will verify your property.";
        $type = 'info';
        $link = '/board-in/bh_manager/verification-status.php?id=' . $bh_id;

        $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('issss', $bh['user_id'], $title, $message, $type, $link);
        $stmt->execute();

        $_SESSION['success'] = 'Visit scheduled successfully. Landlord has been notified.';

    } elseif ($action === 'reject') {
        $rejection_notes = trim($_POST['rejection_notes'] ?? '');
        $block_user = isset($_POST['block_user']) ? 1 : 0;

        if (empty($rejection_notes)) {
            throw new Exception('Rejection reason is required.');
        }

        // Update boarding house
        $stmt = $conn->prepare("
            UPDATE boarding_houses 
            SET verification_status = 'rejected',
                verification_notes = ?,
                verification_rejection_count = verification_rejection_count + 1
            WHERE id = ?
        ");
        $stmt->bind_param('si', $rejection_notes, $bh_id);
        $stmt->execute();

        // If blocking user, suspend their account
        if ($block_user) {
            $stmt = $conn->prepare("UPDATE users SET status = 'suspended' WHERE id = ?");
            $stmt->bind_param('i', $bh['user_id']);
            $stmt->execute();
        }

        // Notify landlord
        $title = 'Verification Request Rejected';
        $message = "Your verification request for '{$bh['title']}' has been rejected. Reason: {$rejection_notes}";
        $type = 'warning';
        $link = '/board-in/bh_manager/verification-status.php?id=' . $bh_id;

        $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('issss', $bh['user_id'], $title, $message, $type, $link);
        $stmt->execute();

        $_SESSION['success'] = 'Verification request rejected. Landlord has been notified.';

    } else {
        throw new Exception('Invalid action.');
    }

    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
}

header('Location: /board-in/admin/verify-bh-queue.php');
exit;