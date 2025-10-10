<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/auth.php';
// require landlords (previously bh_manager) or admin
require_role(['landlord','admin']);
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Add Listing</h2>
<form method="post" action="/board-in/backend/process-add.php" enctype="multipart/form-data">
  <div class="mb-3"><label class="form-label">Title</label><input name="title" class="form-control" required></div>
  <div class="mb-3"><label class="form-label">Name (display)</label><input name="name" class="form-control"></div>
  <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control"></textarea></div>
  <!-- Latitude/Longitude inputs removed — geo/map feature deprecated -->
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Monthly rent (PHP)</label><input name="monthly_rent" type="number" step="0.01" class="form-control"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Security deposit (PHP)</label><input name="security_deposit" type="number" step="0.01" class="form-control"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Available rooms</label><input name="available_rooms" type="number" class="form-control"></div>
  </div>
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Total rooms</label><input name="total_rooms" type="number" class="form-control"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Gender allowed</label>
      <select name="gender_allowed" class="form-select">
        <option value="both">Both</option>
        <option value="male">Male</option>
        <option value="female">Female</option>
      </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Curfew time (optional)</label><input name="curfew_time" type="time" class="form-control"></div>
  </div>
  <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control"></textarea></div>
  <div class="mb-3"><label class="form-label">House rules</label><textarea name="house_rules" class="form-control"></textarea></div>
  <div class="mb-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select">
      <option value="available">Available</option>
      <option value="full">Full</option>
      <option value="inactive">Inactive</option>
    </select>
  </div>
  <div class="mb-3">
    <label class="form-label">Amenities</label>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="wifi" id="feat-wifi"><label class="form-check-label" for="feat-wifi">WiFi</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="own_cr" id="feat-owncr"><label class="form-check-label" for="feat-owncr">Own CR</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="shared_kitchen" id="feat-sharedkitchen"><label class="form-check-label" for="feat-sharedkitchen">Shared kitchen</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="laundry" id="feat-laundry"><label class="form-check-label" for="feat-laundry">Laundry</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="parking" id="feat-parking"><label class="form-check-label" for="feat-parking">Parking</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="study_area" id="feat-study"><label class="form-check-label" for="feat-study">Study area</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="air_conditioning" id="feat-ac"><label class="form-check-label" for="feat-ac">Air conditioning</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="water_heater" id="feat-waterheater"><label class="form-check-label" for="feat-waterheater">Water heater</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="bipsu" id="feat-bipsu"><label class="form-check-label" for="feat-bipsu">Close to BIPSU</label></div>
  </div>
  <div class="mb-3"><label class="form-label">Images (up to 10)</label><input name="images[]" type="file" multiple class="form-control"></div>
  <button class="btn btn-primary">Create</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
