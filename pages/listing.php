<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['error'] = 'Listing not found';
    header('Location: /board-in/pages/search.php');
    exit;
}

// Handle review/comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user'])) {
        $_SESSION['error'] = 'Please log in to submit a review or comment';
        header('Location: /board-in/pages/login.php');
        exit;
    }

    $user_id = $_SESSION['user']['id'];
    $comment = trim($_POST['comment'] ?? '');
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;

    // Validate input
    if (empty($comment)) {
        $_SESSION['error'] = 'Please write a comment';
    } elseif (strlen($comment) < 10) {
        $_SESSION['error'] = 'Comment must be at least 10 characters long';
    } else {
        // Check if user has paid booking for this listing (for star ratings)
        $has_paid_booking = false;
        if ($rating > 0) {
            $stmt_booking = $conn->prepare('
                SELECT id FROM bookings 
                WHERE bh_id = ? AND user_id = ? AND payment_status = "paid" 
                AND status IN ("confirmed", "completed")
                LIMIT 1
            ');
            $stmt_booking->bind_param('ii', $id, $user_id);
            $stmt_booking->execute();
            $res_booking = $stmt_booking->get_result();
            $has_paid_booking = $res_booking->num_rows > 0;

            if (!$has_paid_booking) {
                $_SESSION['error'] = 'Only students with paid bookings can leave star ratings';
                $rating = 0; // Reset rating if not allowed
            }
        }

        // Insert as comment if no rating, or as review if rating exists
        if ($rating > 0) {
            // Insert as review
            $stmt = $conn->prepare('
                INSERT INTO reviews (bh_id, listing_id, user_id, student_id, rating, comment, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, "approved", NOW())
            ');
            $stmt->bind_param('iiiiis', $id, $id, $user_id, $user_id, $rating, $comment);
        } else {
            // Insert as comment only
            $stmt = $conn->prepare('
                INSERT INTO comments (bh_id, user_id, comment, status, created_at) 
                VALUES (?, ?, ?, "approved", NOW())
            ');
            $stmt->bind_param('iis', $id, $user_id, $comment);
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = $rating > 0 ? 'Thank you for your review!' : 'Thank you for your comment!';
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            $_SESSION['error'] = 'Failed to submit. Please try again.';
        }
    }
}

// Enhanced query to include landlord profile picture
$stmt = $conn->prepare('SELECT bh.*, u.full_name AS landlord_name, u.contact_number AS landlord_contact, u.profile_picture AS landlord_profile_pic FROM boarding_houses bh LEFT JOIN users u ON u.id = bh.manager_id WHERE bh.id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$listing = $res->fetch_assoc();

if (!$listing) {
    $_SESSION['error'] = 'Listing not found';
    header('Location: /board-in/pages/search.php');
    exit;
}

// CRITICAL: Check if user can view this listing
$is_admin = !empty($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
$is_landlord = !empty($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'landlord';
$is_owner = !empty($_SESSION['user']['id']) && $_SESSION['user']['id'] == $listing['user_id'];

// Only show active/available listings to non-admins who don't own the listing
if (!$is_admin && !$is_owner && !in_array($listing['status'], ['active', 'available'])) {
    $_SESSION['error'] = 'This listing is not available for viewing. It may be pending approval or has been removed.';
    header('Location: /board-in/pages/search.php');
    exit;
}

// Show status banner for admin/owner
$show_status_banner = ($is_admin || $is_owner) && !in_array($listing['status'], ['active', 'available']);

$can_request_verification = (
    $is_owner && 
    in_array($listing['status'], ['active', 'available']) && 
    $listing['verification_status'] !== 'verified' &&
    $listing['verification_status'] !== 'pending_verification' &&
    $listing['verification_rejection_count'] < 3
);

// Check if current user has paid booking (for review permissions)
$user_has_paid_booking = false;
if (isset($_SESSION['user']['id'])) {
    $user_id = $_SESSION['user']['id'];
    $stmt_booking = $conn->prepare('
        SELECT id FROM bookings 
        WHERE bh_id = ? AND user_id = ? AND payment_status = "paid" 
        AND status IN ("confirmed", "completed")
        LIMIT 1
    ');
    $stmt_booking->bind_param('ii', $id, $user_id);
    $stmt_booking->execute();
    $res_booking = $stmt_booking->get_result();
    $user_has_paid_booking = $res_booking->num_rows > 0;
}

// photos
$photos = [];
$stmt2 = $conn->prepare('SELECT photo_url, is_primary FROM photos WHERE boarding_house_id = ? ORDER BY is_primary DESC, id ASC');
$stmt2->bind_param('i', $id);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($r = $res2->fetch_assoc()) $photos[] = $r;

// amenities
$amen = [];
$stmt3 = $conn->prepare('SELECT * FROM amenities WHERE boarding_house_id = ? LIMIT 1');
$stmt3->bind_param('i', $id);
$stmt3->execute();
$res3 = $stmt3->get_result();
if ($res3) $amen = $res3->fetch_assoc();

// reviews (only from users with paid bookings)
$reviews = [];
$stmt4 = $conn->prepare('SELECT r.*, u.full_name FROM reviews r LEFT JOIN users u ON u.id = r.student_id WHERE r.listing_id = ? AND r.status = "approved" ORDER BY r.created_at DESC');
$stmt4->bind_param('i', $id);
$stmt4->execute();
$res4 = $stmt4->get_result();
while ($r = $res4->fetch_assoc()) $reviews[] = $r;

// comments (from all users)
$comments = [];
$stmt5 = $conn->prepare('SELECT c.*, u.full_name, u.profile_picture FROM comments c LEFT JOIN users u ON u.id = c.user_id WHERE c.bh_id = ? AND c.status = "approved" ORDER BY c.created_at DESC');
$stmt5->bind_param('i', $id);
$stmt5->execute();
$res5 = $stmt5->get_result();
while ($r = $res5->fetch_assoc()) $comments[] = $r;

// Calculate average rating from reviews only
$avgRating = 0;
if (!empty($reviews)) {
    $totalRating = array_sum(array_column($reviews, 'rating'));
    $avgRating = round($totalRating / count($reviews), 1);
}

// Get landlord profile picture
$landlord_profile_pic = $listing['landlord_profile_pic'] 
    ? PROFILE_UPLOAD_URL . $listing['landlord_profile_pic']
    : 'https://ui-avatars.com/api/?name=' . urlencode($listing['landlord_name'] ?? 'Landlord') . '&size=200&background=667eea&color=fff';

// Check if user has favorited this listing
$is_favorited = false;
if (isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
    $user_role = $_SESSION['user']['role'] ?? '';
    
    $stmt_fav = $conn->prepare('SELECT id FROM favorites WHERE user_id = ? AND listing_id = ?');
    $stmt_fav->bind_param('ii', $user_id, $id);
    $stmt_fav->execute();
    $res_fav = $stmt_fav->get_result();
    $is_favorited = $res_fav->num_rows > 0;
}

?>

<!-- Add CSS reference -->
<link rel="stylesheet" href="/board-in/assets/css/listing.css">

<!-- Status Banner for Admin/Owner -->
<?php if ($show_status_banner): ?>
<div class="container mt-4 fade-in-up">
    <div class="alert alert-warning d-flex align-items-center border-0 shadow-sm" style="border-radius: var(--border-radius);">
        <i class="bi bi-<?php echo $listing['status'] === 'pending' ? 'clock' : 'x-circle'; ?> me-2 fs-4"></i>
        <div class="flex-grow-1">
            <strong>Listing Status: <?php echo ucfirst($listing['status']); ?></strong>
            <?php if ($listing['status'] === 'pending'): ?>
                <p class="mb-0">This listing is awaiting admin approval and is not visible to students yet.</p>
            <?php elseif ($listing['status'] === 'rejected'): ?>
                <p class="mb-0">This listing has been rejected by an administrator and is not visible to students.</p>
            <?php else: ?>
                <p class="mb-0">This listing is currently not visible to students.</p>
            <?php endif; ?>
        </div>
        <?php if ($is_admin): ?>
            <a href="/board-in/admin/manage-listings.php" class="btn btn-sm btn-light ms-3">
                <i class="bi bi-gear"></i> Manage
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="container-fluid px-0">
    <!-- Enhanced Image Gallery -->
    <div class="gallery-container fade-in-up">
        <?php if (!empty($photos)): ?>
            <div class="gallery-main">
                <?php foreach (array_slice($photos, 0, 5) as $idx => $p): ?>
                    <div class="gallery-item hover-lift">
                        <img src="<?php echo htmlspecialchars($p['photo_url']); ?>" 
                             alt="<?php echo htmlspecialchars($listing['title']); ?>"
                             loading="lazy">
                        <?php if ($idx === 0): ?>
                            <div class="gallery-overlay">
                                <h5 class="mb-1"><?php echo htmlspecialchars($listing['title']); ?></h5>
                                <p class="mb-0 text-sm">Click to view full image</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="gallery-count">
                <i class="bi bi-image me-1"></i><?php echo count($photos); ?> photos
            </div>
        <?php else: ?>
            <div class="gallery-main">
                <div class="gallery-item" style="grid-row: 1 / -1;">
                    <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=1200&h=600&fit=crop" 
                         alt="<?php echo htmlspecialchars($listing['title']); ?>"
                         loading="lazy">
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <!-- Enhanced Verification Banner -->
    <div class="verification-banner-modern fade-in-up">
        <?php if ($listing['verification_status'] === 'verified'): ?>
            <div class="verification-card verified d-flex align-items-center">
                <i class="bi bi-patch-check-fill fs-1 text-success me-3"></i>
                <div class="flex-grow-1">
                    <h5 class="mb-1 fw-bold">✓ Verified Property</h5>
                    <p class="mb-0 text-muted">This boarding house has been physically verified by our team on <?php echo date('M d, Y', strtotime($listing['last_verified_at'] ?? $listing['verification_completed_at'])); ?></p>
                </div>
            </div>
        <?php elseif ($listing['verification_status'] === 'pending_verification'): ?>
            <div class="verification-card pending d-flex align-items-center">
                <i class="bi bi-clock-history fs-1 text-warning me-3"></i>
                <div class="flex-grow-1">
                    <h5 class="mb-1 fw-bold">⏳ Verification Pending</h5>
                    <p class="mb-0 text-muted">This property is undergoing verification review</p>
                </div>
                <?php if ($is_owner): ?>
                    <a href="/board-in/bh_manager/verification-status.php?id=<?php echo $listing['id']; ?>" class="btn btn-warning btn-modern">
                        Track Status
                    </a>
                <?php endif; ?>
            </div>
        <?php elseif ($can_request_verification): ?>
            <div class="verification-card unverified d-flex align-items-center">
                <i class="bi bi-shield-exclamation fs-1 text-secondary me-3"></i>
                <div class="flex-grow-1">
                    <h5 class="mb-1 fw-bold">Get Verified</h5>
                    <p class="mb-0 text-muted">Build trust with students by getting your property verified!</p>
                </div>
                <a href="/board-in/bh_manager/request-verification.php?id=<?php echo $listing['id']; ?>" class="btn btn-primary-modern">
                    <i class="bi bi-patch-check"></i> Get Verified
                </a>
            </div>
        <?php else: ?>
            <div class="verification-card unverified d-flex align-items-center">
                <i class="bi bi-shield-x fs-1 text-muted me-3"></i>
                <div>
                    <p class="mb-0 text-muted">Unverified Property</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Enhanced Header Section -->
            <div class="listing-header fade-in-up">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-start gap-3">
                            <h1 class="listing-title"><?php echo htmlspecialchars($listing['title']); ?></h1>
                            
                            <!-- Heart Favorite Button - Moved to header -->
                            <?php if (isset($_SESSION['user'])): ?>
                            <button id="favoriteBtn" 
                                    class="btn-heart-favorite <?php echo $is_favorited ? 'favorited' : ''; ?>" 
                                    data-listing-id="<?php echo $id; ?>"
                                    title="<?php echo $is_favorited ? 'Remove from favorites' : 'Add to favorites'; ?>">
                                <i class="bi bi-heart<?php echo $is_favorited ? '-fill' : ''; ?>"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="listing-location">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span><?php echo nl2br(htmlspecialchars($listing['address'])); ?></span>
                        </div>

                        <div class="price-section">
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="price-main">₱<?php echo number_format($listing['monthly_rent'], 0); ?></span>
                                <span class="price-period">/ month</span>
                            </div>
                            
                            <?php if ($avgRating > 0): ?>
                                <div class="rating-badge">
                                    <div class="rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?php echo $i <= $avgRating ? '-fill' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span><?php echo $avgRating; ?></span>
                                    <span class="text-muted">(<?php echo count($reviews); ?> reviews)</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Enhanced Availability Badge -->
                        <?php 
                        $available = $listing['available_rooms'];
                        $total = $listing['total_rooms'];
                        $availClass = $available == 0 ? 'full' : ($available <= 3 ? 'limited' : 'available');
                        ?>
                        <div class="availability-badge <?php echo $availClass; ?>">
                            <i class="bi bi-<?php echo $available > 0 ? 'check-circle' : 'x-circle'; ?>"></i>
                            <?php if ($available > 0): ?>
                                <?php echo $available; ?> of <?php echo $total; ?> rooms available
                            <?php else: ?>
                                Fully booked
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Amenities Section -->
            <div class="info-section-modern fade-in-up">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-stars"></i>
                    </div>
                    <h3 class="section-title">Amenities</h3>
                </div>
                <div class="amenity-grid-modern">
                    <?php if ($amen): ?>
                        <?php if ($amen['wifi']): ?>
                            <div class="amenity-item-modern">
                                <div class="amenity-icon-modern"><i class="bi bi-wifi"></i></div>
                                <span class="amenity-label-modern">WiFi</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($amen['own_cr']): ?>
                            <div class="amenity-item-modern">
                                <div class="amenity-icon-modern"><i class="bi bi-door-closed"></i></div>
                                <span class="amenity-label-modern">Own CR</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($amen['shared_kitchen']): ?>
                            <div class="amenity-item-modern">
                                <div class="amenity-icon-modern"><i class="bi bi-cup-hot"></i></div>
                                <span class="amenity-label-modern">Shared Kitchen</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($amen['laundry_area']): ?>
                            <div class="amenity-item-modern">
                                <div class="amenity-icon-modern"><i class="bi bi-droplet"></i></div>
                                <span class="amenity-label-modern">Laundry Area</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($amen['parking']): ?>
                            <div class="amenity-item-modern">
                                <div class="amenity-icon-modern"><i class="bi bi-car-front"></i></div>
                                <span class="amenity-label-modern">Parking</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($amen['study_area']): ?>
                            <div class="amenity-item-modern">
                                <div class="amenity-icon-modern"><i class="bi bi-book"></i></div>
                                <span class="amenity-label-modern">Study Area</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($amen['air_conditioning']): ?>
                            <div class="amenity-item-modern">
                                <div class="amenity-icon-modern"><i class="bi bi-snow"></i></div>
                                <span class="amenity-label-modern">Air Conditioning</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($amen['water_heater']): ?>
                            <div class="amenity-item-modern">
                                <div class="amenity-icon-modern"><i class="bi bi-thermometer-sun"></i></div>
                                <span class="amenity-label-modern">Water Heater</span>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted">No amenities listed</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Enhanced House Rules Section -->
            <?php if (!empty($listing['house_rules'])): ?>
            <div class="info-section-modern fade-in-up">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <h3 class="section-title">House Rules</h3>
                </div>
                <p class="text-muted lh-lg"><?php echo nl2br(htmlspecialchars($listing['house_rules'])); ?></p>
            </div>
            <?php endif; ?>

            <!-- Enhanced Reviews & Comments Section -->
            <div class="info-section-modern fade-in-up">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-chat-quote"></i>
                    </div>
                    <h3 class="section-title">Reviews & Comments</h3>
                </div>

                <!-- Review/Comment Form -->
                <?php if (isset($_SESSION['user'])): ?>
                <div class="review-form-modern mb-4">
                    <form method="POST" id="reviewForm">
                        <div class="form-group">
                            <label for="comment" class="form-label fw-bold">Share your experience</label>
                            <textarea class="form-control" id="comment" name="comment" rows="4" 
                                      placeholder="Share your thoughts about this boarding house..." 
                                      required></textarea>
                        </div>

                        <!-- Star Rating (only for users with paid bookings) -->
                        <?php if ($user_has_paid_booking): ?>
                        <div class="rating-section mb-3">
                            <label class="form-label fw-bold">Your Rating</label>
                            <div class="star-rating">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo $i == 5 ? 'checked' : ''; ?>>
                                    <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> stars">
                                        <i class="bi bi-star-fill"></i>
                                    </label>
                                <?php endfor; ?>
                            </div>
                            <small class="text-muted">Only students with paid bookings can leave star ratings</small>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info py-2">
                            <small>
                                <i class="bi bi-info-circle"></i> 
                                Only students with paid bookings can leave star ratings. 
                                You can still share your comments and questions.
                            </small>
                        </div>
                        <?php endif; ?>

                        <button type="submit" name="submit_review" class="btn btn-primary-modern">
                            <i class="bi bi-send me-2"></i>Submit
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <a href="/board-in/pages/login.php" class="alert-link">Log in</a> to share your comments and reviews.
                </div>
                <?php endif; ?>

                <!-- Reviews (with ratings) -->
                <?php if (!empty($reviews)): ?>
                    <h5 class="mb-3">Student Reviews (<?php echo count($reviews); ?>)</h5>
                    <?php foreach ($reviews as $rv): ?>
                        <div class="review-card-modern">
                            <div class="d-flex align-items-start gap-3">
                                <div class="review-avatar-modern">
                                    <?php echo strtoupper(substr($rv['full_name'] ?? 'S', 0, 1)); ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="review-meta-modern">
                                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($rv['full_name'] ?? 'Student'); ?></h6>
                                        <div class="rating-stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?php echo $i <= $rv['rating'] ? '-fill' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="text-muted"><?php echo date('M j, Y', strtotime($rv['created_at'])); ?></span>
                                    </div>
                                    <p class="text-muted mb-0 mt-2"><?php echo nl2br(htmlspecialchars($rv['comment'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Comments (without ratings) -->
                <?php if (!empty($comments)): ?>
                    <h5 class="mb-3 mt-4">Comments (<?php echo count($comments); ?>)</h5>
                    <?php foreach ($comments as $cmt): ?>
                        <div class="comment-card-modern">
                            <div class="d-flex align-items-start gap-3">
                                <div class="comment-avatar-modern">
                                    <?php 
                                    if (!empty($cmt['profile_picture'])) {
                                        echo '<img src="' . PROFILE_UPLOAD_URL . htmlspecialchars($cmt['profile_picture']) . '" alt="' . htmlspecialchars($cmt['full_name']) . '">';
                                    } else {
                                        echo strtoupper(substr($cmt['full_name'] ?? 'U', 0, 1));
                                    }
                                    ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="comment-meta-modern">
                                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($cmt['full_name'] ?? 'User'); ?></h6>
                                        <span class="text-muted"><?php echo date('M j, Y', strtotime($cmt['created_at'])); ?></span>
                                    </div>
                                    <p class="text-muted mb-0 mt-2"><?php echo nl2br(htmlspecialchars($cmt['comment'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (empty($reviews) && empty($comments)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-chat fs-1 text-muted mb-3 d-block"></i>
                        <h4 class="text-muted">No reviews or comments yet</h4>
                        <p class="text-muted">Be the first to share your experience!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Enhanced Sidebar -->
        <div class="col-lg-4">
            <div class="contact-card-modern fade-in-up">
                <div class="contact-header">
                    <div class="contact-avatar">
                        <img src="<?php echo htmlspecialchars($landlord_profile_pic); ?>" 
                             alt="<?php echo htmlspecialchars($listing['landlord_name'] ?? 'Landlord'); ?>">
                    </div>
                    <h5 class="fw-bold mb-0">Contact Landlord</h5>
                </div>
                
                <div class="contact-info-modern">
                    <div class="contact-icon-modern">
                        <i class="bi bi-person"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Landlord</small>
                        <strong><?php echo htmlspecialchars($listing['landlord_name'] ?? 'Owner'); ?></strong>
                    </div>
                </div>

                <div class="contact-info-modern">
                    <div class="contact-icon-modern">
                        <i class="bi bi-telephone"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Phone</small>
                        <strong><?php echo htmlspecialchars($listing['contact_phone'] ?? $listing['landlord_contact'] ?? 'Not available'); ?></strong>
                    </div>
                </div>

                <?php if ($listing['available_rooms'] > 0 && in_array($listing['status'], ['active', 'available'])): ?>
                    <a href="/board-in/student/booking.php?id=<?php echo $listing['id']; ?>" 
                       class="btn btn-primary-modern w-100 mt-3">
                        <i class="bi bi-calendar-check me-2"></i>Book Now
                    </a>
                <?php elseif ($listing['available_rooms'] == 0): ?>
                    <button class="btn btn-secondary-modern w-100 mt-3" disabled>
                        <i class="bi bi-x-circle me-2"></i>Fully Booked
                    </button>
                <?php else: ?>
                    <button class="btn btn-secondary-modern w-100 mt-3" disabled>
                        <i class="bi bi-clock me-2"></i>Pending Approval
                    </button>
                <?php endif; ?>

                <a href="/board-in/pages/search.php" class="btn btn-secondary-modern w-100 mt-2">
                    <i class="bi bi-arrow-left me-2"></i>Back to Search
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<!-- Add CSS for the review/comment system -->
<style>
.review-form-modern {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    border: 1px solid var(--border-color);
}

.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 0.25rem;
}

.star-rating input {
    display: none;
}

.star-rating label {
    cursor: pointer;
    font-size: 1.5rem;
    color: #ddd;
    transition: color 0.2s;
}

.star-rating input:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label {
    color: #ffc107;
}

.star-rating input:checked + label {
    color: #ffc107;
}

.rating-section {
    padding: 1rem 0;
}

.comment-card-modern {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    padding: 1.25rem;
    border: 1px solid var(--border-color);
    margin-bottom: 1rem;
}

.comment-avatar-modern {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    flex-shrink: 0;
}

.comment-avatar-modern img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.comment-meta-modern {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.comment-meta-modern h6 {
    margin-bottom: 0;
}

.comment-meta-modern .text-muted {
    font-size: 0.875rem;
}
</style>

<!-- Add JS reference -->
<script src="/board-in/assets/js/listing.js"></script>