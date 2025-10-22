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
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="bi bi-plus-circle"></i> Add New Listing</h2>
                    <p class="text-muted mb-0">Create a new boarding house listing for approval</p>
                </div>
                <a href="/board-in/bh_manager/my-listings.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to My Listings
                </a>
            </div>
            
            <!-- Information Alert -->
            <div class="alert alert-info d-flex align-items-center" role="alert">
                <i class="bi bi-info-circle fs-4 me-3"></i>
                <div>
                    <strong>Approval Required:</strong> Your listing will be submitted for admin approval and will be visible to students once approved.
                </div>
            </div>
        </div>
    </div>
    
    <form method="post" action="/board-in/backend/process-add.php" enctype="multipart/form-data">
        
        <!-- Basic Information -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Basic Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input name="title" class="form-control" required placeholder="e.g., Cozy Boarding House near BIPSU">
                    <small class="form-text text-muted">This will be the main title displayed to students</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Display Name</label>
                    <input name="name" class="form-control" placeholder="Leave blank to use title">
                    <small class="form-text text-muted">Optional: A shorter display name for the listing</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Describe your boarding house, its features, and what makes it special..."></textarea>
                    <small class="form-text text-muted">Provide a detailed description to attract students</small>
                </div>
            </div>
        </div>

        <!-- Location Information -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Location Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Complete Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Enter the full address here"></textarea>
                    <small class="form-text text-muted">Include street name, barangay, and landmarks</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">City/Municipality</label>
                    <input name="city" class="form-control" placeholder="e.g., Naval, Biliran">
                </div>

                <div class="mb-3">
                    <label class="form-label">Contact Number</label>
                    <input name="contact_phone" type="tel" class="form-control" placeholder="e.g., 09123456789">
                    <small class="form-text text-muted">Students will use this to contact you</small>
                </div>
            </div>
        </div>

        <!-- Room & Pricing Details -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-door-open"></i> Room & Pricing Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Room Type <span class="text-danger">*</span></label>
                        <select name="room_type" class="form-select" required>
                            <option value="">Select room type</option>
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
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> House Rules & Policies</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">House Rules</label>
                    <textarea name="house_rules" class="form-control" rows="3" placeholder="Enter house rules and regulations..."></textarea>
                    <small class="form-text text-muted">Examples: No smoking, No overnight guests, Quiet hours, etc.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Curfew Time (optional)</label>
                    <input name="curfew_time" type="time" class="form-control">
                    <small class="form-text text-muted">Leave blank if no curfew</small>
                </div>
            </div>
        </div>

        <!-- Amenities -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-stars"></i> Amenities</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Select all amenities that your boarding house offers:</p>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="wifi" id="feat-wifi">
                            <label class="form-check-label" for="feat-wifi">
                                <i class="bi bi-wifi"></i> WiFi
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="own_cr" id="feat-owncr">
                            <label class="form-check-label" for="feat-owncr">
                                <i class="bi bi-door-closed"></i> Own CR
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="shared_kitchen" id="feat-sharedkitchen">
                            <label class="form-check-label" for="feat-sharedkitchen">
                                <i class="bi bi-cup-hot"></i> Shared Kitchen
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="laundry" id="feat-laundry">
                            <label class="form-check-label" for="feat-laundry">
                                <i class="bi bi-droplet"></i> Laundry Area
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="parking" id="feat-parking">
                            <label class="form-check-label" for="feat-parking">
                                <i class="bi bi-car-front"></i> Parking
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="study_area" id="feat-study">
                            <label class="form-check-label" for="feat-study">
                                <i class="bi bi-book"></i> Study Area
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="air_conditioning" id="feat-ac">
                            <label class="form-check-label" for="feat-ac">
                                <i class="bi bi-snow"></i> Air Conditioning
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="water_heater" id="feat-waterheater">
                            <label class="form-check-label" for="feat-waterheater">
                                <i class="bi bi-thermometer-sun"></i> Water Heater
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="bipsu" id="feat-bipsu">
                            <label class="form-check-label" for="feat-bipsu">
                                <i class="bi bi-mortarboard"></i> Close to BIPSU
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Images -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-images"></i> Property Images</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Upload Images (up to 10)</label>
                    <input name="images[]" type="file" multiple accept="image/*" class="form-control">
                    <small class="form-text text-muted">Accepted formats: JPG, PNG, GIF. Max size: 5MB per image. First image will be the main photo.</small>
                </div>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> <strong>Tip:</strong> High-quality images increase your chances of getting approved and attracting students!
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="mb-4">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="/board-in/bh_manager/my-listings.php" class="btn btn-secondary btn-lg">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-send"></i> Submit for Approval
                </button>
            </div>
            <small class="text-muted d-block mt-2 text-end">
                * Your listing will be reviewed by an administrator before becoming visible to students
            </small>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>