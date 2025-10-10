<?php
require_once __DIR__ . '/../config/session.php';
require_role(['landlord','admin']);
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Landlord Verification</h2>
<p>Please upload the required documents to verify your landlord account. Documents will be reviewed by admin.</p>
<form method="post" action="/board-in/backend/process-verification.php" enctype="multipart/form-data">
  <div class="mb-3">
    <label class="form-label">Business name (optional)</label>
    <input name="business_name" class="form-control">
  </div>
  <div class="mb-3">
    <label class="form-label">Valid ID (front/back)</label>
    <input name="valid_id" type="file" accept="image/*" class="form-control">
  </div>
  <div class="mb-3">
    <label class="form-label">Proof of ownership / lease</label>
    <input name="proof_of_ownership" type="file" accept="image/*" class="form-control">
  </div>
  <div class="mb-3">
    <label class="form-label">Barangay clearance (optional)</label>
    <input name="barangay_clearance" type="file" accept="image/*" class="form-control">
  </div>
  <div class="mb-3">
    <label class="form-label">GCash number</label>
    <input name="gcash_number" class="form-control">
  </div>
  <button class="btn btn-primary">Submit for verification</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
