<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/auth.php';
// require landlords (previously bh_manager) or admin
require_role(['landlord','admin']);
require_once __DIR__ . '/../includes/header.php';

// Display any error or success messages
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
    echo htmlspecialchars($_SESSION['error']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
    echo htmlspecialchars($_SESSION['success']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    unset($_SESSION['success']);
}
?>

<div class="container mt-4">
    <h2>Add New Listing</h2>
    
    <form method="post" action="/board-in/backend/process-add.php" enctype="multipart/form-data">
        
        <!-- Basic Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Basic Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input name="title" class="form-control" required placeholder="e.g., Cozy Boarding House near BIPSU">
                </div>

                <div class="mb-3">
                    <label class="form-label">Display Name</label>
                    <input name="name" class="form-control" placeholder="Leave blank to use title">
                    <small class="form-text text-muted">Optional: A shorter display name for the listing</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Describe your boarding house..."></textarea>
                </div>
            </div>
        </div>

        <!-- Location Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Location Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Complete Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Enter the full address here"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">City/Municipality</label>
                    <input name="city" class="form-control" placeholder="e.g., Tagbilaran City">
                </div>

                <div class="mb-3">
                    <label class="form-label">Contact Number</label>
                    <input name="contact_phone" type="tel" class="form-control" placeholder="e.g., 09123456789">
                </div>
            </div>
        </div>

        <!-- Room & Pricing Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Room & Pricing Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Room Type <span class="text-danger">*</span></label>
                        <select name="room_type" class="form-select" required>
                            <option value="single">Single Room</option>
                            <option value="shared">Shared Room</option>
                            <option value="studio">Studio Type</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Total Rooms</label>
                        <input name="total_rooms" type="number" min="1" class="form-control" placeholder="Total number of rooms">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Available Rooms</label>
                        <input name="available_rooms" type="number" min="0" class="form-control" placeholder="Currently available">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Monthly Rent (PHP)</label>
                        <input name="monthly_rent" type="number" step="0.01" min="0" class="form-control" placeholder="0.00">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Security Deposit (PHP)</label>
                        <input name="security_deposit" type="number" step="0.01" min="0" class="form-control" placeholder="0.00">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Gender Allowed</label>
                        <select name="gender_allowed" class="form-select">
                            <option value="both">Both</option>
                            <option value="male">Male Only</option>
                            <option value="female">Female Only</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- House Rules & Policies -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">House Rules & Policies</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">House Rules</label>
                    <textarea name="house_rules" class="form-control" rows="3" placeholder="Enter house rules and regulations..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Curfew Time (optional)</label>
                    <input name="curfew_time" type="time" class="form-control">
                    <small class="form-text text-muted">Leave blank if no curfew</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Listing Status</label>
                    <select name="status" class="form-select">
                        <option value="available">Available</option>
                        <option value="full">Full</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Amenities -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Amenities</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="wifi" id="feat-wifi">
                            <label class="form-check-label" for="feat-wifi">WiFi</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="own_cr" id="feat-owncr">
                            <label class="form-check-label" for="feat-owncr">Own CR</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="shared_kitchen" id="feat-sharedkitchen">
                            <label class="form-check-label" for="feat-sharedkitchen">Shared Kitchen</label>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="laundry" id="feat-laundry">
                            <label class="form-check-label" for="feat-laundry">Laundry Area</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="parking" id="feat-parking">
                            <label class="form-check-label" for="feat-parking">Parking</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="study_area" id="feat-study">
                            <label class="form-check-label" for="feat-study">Study Area</label>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="air_conditioning" id="feat-ac">
                            <label class="form-check-label" for="feat-ac">Air Conditioning</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="water_heater" id="feat-waterheater">
                            <label class="form-check-label" for="feat-waterheater">Water Heater</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="bipsu" id="feat-bipsu">
                            <label class="form-check-label" for="feat-bipsu">Close to BIPSU</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Images -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Property Images</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Upload Images (up to 10)</label>
                    <input name="images[]" type="file" multiple accept="image/*" class="form-control">
                    <small class="form-text text-muted">Accepted formats: JPG, PNG, GIF. Max size: 5MB per image.</small>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="mb-4">
            <button type="submit" class="btn btn-primary btn-lg">Create Listing</button>
            <a href="/board-in/bh_manager/my-listings.php" class="btn btn-secondary btn-lg">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>