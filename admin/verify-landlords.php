<?php
require_once __DIR__ . '/../config/session.php';
require_role(['admin']);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

$stmt = $conn->prepare("SELECT l.id, l.user_id, l.business_name, l.valid_id_url, l.proof_of_ownership_url, l.barangay_clearance_url, l.verification_status, u.email, u.full_name FROM landlords l JOIN users u ON u.id = l.user_id WHERE l.verification_status = 'pending' ORDER BY l.created_at ASC");
$stmt->execute();
$res = $stmt->get_result();
?>

<h2>Landlord Verification Queue</h2>
<?php while ($row = $res->fetch_assoc()): ?>
  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title"><?php echo esc_attr($row['business_name'] ?: $row['full_name']); ?></h5>
      <p class="card-text">User: <?php echo esc_attr($row['email']); ?></p>
      <p>
        <?php if ($row['valid_id_url']): ?><a href="<?php echo $row['valid_id_url']; ?>" target="_blank">Valid ID</a><?php endif; ?>
        <?php if ($row['proof_of_ownership_url']): ?> | <a href="<?php echo $row['proof_of_ownership_url']; ?>" target="_blank">Proof</a><?php endif; ?>
        <?php if ($row['barangay_clearance_url']): ?> | <a href="<?php echo $row['barangay_clearance_url']; ?>" target="_blank">Barangay</a><?php endif; ?>
      </p>
      <form method="post" action="/board-in/backend/process-verify-action.php" class="d-inline">
        <input type="hidden" name="landlord_id" value="<?php echo $row['id']; ?>">
        <input type="hidden" name="action" value="approve">
        <button class="btn btn-success btn-sm">Approve</button>
      </form>
      <form method="post" action="/board-in/backend/process-verify-action.php" class="d-inline ms-2">
        <input type="hidden" name="landlord_id" value="<?php echo $row['id']; ?>">
        <input type="hidden" name="action" value="reject">
        <input name="notes" placeholder="Notes (optional)" class="form-control form-control-sm d-inline-block" style="width:300px;">
        <button class="btn btn-danger btn-sm ms-1">Reject</button>
      </form>
    </div>
  </div>
<?php endwhile; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
