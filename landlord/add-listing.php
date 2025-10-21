<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
require_login();
?>

<h2>Add New Listing</h2>

<form action="../backend/process-add.php" method="POST" enctype="multipart/form-data">
  <div class="mb-3">
    <label class="form-label">Boarding House / Room Name</label>
    <input type="text" name="title" class="form-control" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Price per Month (₱)</label>
    <input type="number" name="price" class="form-control" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Location</label>
    <input type="text" name="location" class="form-control" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Amenities</label>
    <textarea name="amenities" class="form-control" rows="3" placeholder="WiFi, Aircon, Water, etc."></textarea>
  </div>

  <div class="mb-3">
  <label class="form-label">🏢 DTI Certificate of Business Name Registration</label>
  <input type="file" name="dti_certificate" accept="image/*" class="form-control" required>
</div>

<div class="mb-3">
  <label class="form-label">📸 Upload Photo of Boarding House / Room</label>
  <input type="file" name="bh_photo" accept="image/*" class="form-control" required>
</div>

  <button type="submit" class="btn btn-primary">Submit Listing</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
