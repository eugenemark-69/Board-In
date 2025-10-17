<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    flash('error', 'Listing not found');
    header('Location: /board-in/pages/search.php');
    exit;
}

$stmt = $conn->prepare('SELECT bh.*, u.full_name AS landlord_name, u.contact_number AS landlord_contact FROM boarding_houses bh LEFT JOIN users u ON u.id = bh.manager_id WHERE bh.id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$listing = $res->fetch_assoc();
if (!$listing) {
    flash('error', 'Listing not found');
    header('Location: /board-in/pages/search.php');
    exit;
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

// reviews
$reviews = [];
$stmt4 = $conn->prepare('SELECT r.*, u.full_name FROM reviews r LEFT JOIN users u ON u.id = r.student_id WHERE r.listing_id = ? ORDER BY r.created_at DESC');
$stmt4->bind_param('i', $id);
$stmt4->execute();
$res4 = $stmt4->get_result();
while ($r = $res4->fetch_assoc()) $reviews[] = $r;

// Calculate average rating
$avgRating = 0;
if (!empty($reviews)) {
    $totalRating = array_sum(array_column($reviews, 'rating'));
    $avgRating = round($totalRating / count($reviews), 1);
}
?>

<!-- Hero Image Section -->
<div class="container-fluid px-0 mb-4">
    <?php if (!empty($photos)): ?>
        <div class="photo-gallery">
            <?php foreach (array_slice($photos, 0, 5) as $idx => $p): ?>
                <div class="photo-gallery-item <?php echo $idx === 0 ? 'photo-gallery-primary' : ''; ?>">
                    <img src="<?php echo esc_attr($p['photo_url']); ?>" 
                         alt="<?php echo esc_attr($listing['title']); ?>" 
                         class="w-100 h-100 object-fit-cover">
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="photo-gallery-item" style="height: 400px;">
            <img src="https://source.unsplash.com/1200x600/?boarding-house" 
                 alt="<?php echo esc_attr($listing['title']); ?>" 
                 class="w-100 h-100 object-fit-cover">
        </div>
    <?php endif; ?>
</div>

<div class="container">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Header Section -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h1 class="mb-2"><?php echo esc_attr($listing['title']); ?></h1>
                        <p class="text-muted mb-0">
                            <i class="bi bi-geo-alt-fill text-primary"></i>
                            <?php echo nl2br(esc_attr($listing['address'])); ?>
                        </p>
                    </div>
                    <?php if ($avgRating > 0): ?>
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?php echo $i <= $avgRating ? '-fill' : ''; ?>"></i>
                            <?php endfor; ?>
                            <span class="rating-number"><?php echo $avgRating; ?></span>
                            <span class="text-muted">(<?php echo count($reviews); ?> reviews)</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Price -->
                <div class="price-display">
                    <span class="price-label">Starting at</span>
                    <span class="price-amount">₱<?php echo number_format($listing['monthly_rent'], 0); ?></span>
                    <span class="price-period">/ month</span>
                </div>

                <!-- Availability Badge -->
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

            <!-- Description Section -->
            <div class="info-section">
                <div class="info-section-header">
                    <div class="info-section-icon">
                        <i class="bi bi-house-door"></i>
                    </div>
                    <h3 class="info-section-title">About This Place</h3>
                </div>
                <p class="text-muted lh-lg"><?php echo nl2br(esc_attr($listing['description'])); ?></p>
            </div>

            <!-- Amenities Section -->
            <div class="info-section">
                <div class="info-section-header">
                    <div class="info-section-icon">
                        <i class="bi bi-stars"></i>
                    </div>
                    <h3 class="info-section-title">Amenities</h3>
                </div>
                <div class="amenity-grid">
                    <?php if ($amen): ?>
                        <?php if ($amen['wifi']): ?>
                            <div class="amenity-item">
                                <div class="amenity-icon"><i class="bi bi-wifi"></i></div>
                                <span class="amenity-label">WiFi</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($amen['own_cr']): ?>
                            <div class="amenity-item">
                                <div class="amenity-icon"><i class="bi bi-door-closed"></i></div>
                                <span class="amenity-label">Own CR</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($amen['shared_kitchen']): ?>
                            <div class="amenity-item">
                                <div class="amenity-icon"><i class="bi bi-cup-hot"></i></div>
                                <span class="amenity-label">Shared Kitchen</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($amen['laundry_area']): ?>
                            <div class="amenity-item">
                                <div class="amenity-icon"><i class="bi bi-droplet"></i></div>
                                <span class="amenity-label">Laundry Area</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($amen['parking']): ?>
                            <div class="amenity-item">
                                <div class="amenity-icon"><i class="bi bi-car-front"></i></div>
                                <span class="amenity-label">Parking</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($amen['study_area']): ?>
                            <div class="amenity-item">
                                <div class="amenity-icon"><i class="bi bi-book"></i></div>
                                <span class="amenity-label">Study Area</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($amen['air_conditioning']): ?>
                            <div class="amenity-item">
                                <div class="amenity-icon"><i class="bi bi-snow"></i></div>
                                <span class="amenity-label">Air Conditioning</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($amen['water_heater']): ?>
                            <div class="amenity-item">
                                <div class="amenity-icon"><i class="bi bi-thermometer-sun"></i></div>
                                <span class="amenity-label">Water Heater</span>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted">No amenities listed</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- House Rules Section -->
            <div class="info-section">
                <div class="info-section-header">
                    <div class="info-section-icon">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <h3 class="info-section-title">House Rules</h3>
                </div>
                <p class="text-muted lh-lg"><?php echo nl2br(esc_attr($listing['house_rules'])); ?></p>
            </div>

            <!-- Location Section -->
            <div class="info-section">
                <div class="info-section-header">
                    <div class="info-section-icon">
                        <i class="bi bi-map"></i>
                    </div>
                    <h3 class="info-section-title">Location</h3>
                </div>
                <div class="map-container">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <p class="text-muted">
                    <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                    <?php echo nl2br(esc_attr($listing['address'])); ?>
                </p>
            </div>

            <!-- Reviews Section -->
            <div class="info-section">
                <div class="info-section-header">
                    <div class="info-section-icon">
                        <i class="bi bi-chat-quote"></i>
                    </div>
                    <h3 class="info-section-title">Student Reviews</h3>
                </div>

                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $rv): ?>
                        <div class="review-card">
                            <div class="d-flex align-items-start gap-3 mb-3">
                                <div class="review-avatar">
                                    <?php echo strtoupper(substr($rv['full_name'] ?? 'S', 0, 1)); ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 fw-bold"><?php echo esc_attr($rv['full_name'] ?? 'Student'); ?></h6>
                                            <div class="review-meta">
                                                <div class="rating-stars">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="bi bi-star<?php echo $i <= $rv['rating'] ? '-fill' : ''; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <span><?php echo date('M Y', strtotime($rv['created_at'])); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-0 mt-2"><?php echo nl2br(esc_attr($rv['comment'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="bi bi-chat"></i>
                        </div>
                        <h3>No reviews yet</h3>
                        <p>Be the first to review this boarding house!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="contact-card">
                <h5 class="fw-bold mb-3">Contact Landlord</h5>
                
                <div class="contact-info">
                    <div class="contact-icon">
                        <i class="bi bi-person"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Landlord</small>
                        <strong><?php echo esc_attr($listing['landlord_name'] ?? 'Owner'); ?></strong>
                    </div>
                </div>

                <div class="contact-info">
                    <div class="contact-icon">
                        <i class="bi bi-telephone"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Phone</small>
                        <strong><?php echo esc_attr($listing['landlord_contact'] ?? 'Not available'); ?></strong>
                    </div>
                </div>

                <?php if ($listing['available_rooms'] > 0): ?>
                    <a href="/board-in/student/booking.php?id=<?php echo $listing['id']; ?>" 
                       class="btn btn-primary w-100 btn-lg mt-3">
                        <i class="bi bi-calendar-check me-2"></i>Book Now
                    </a>
                <?php else: ?>
                    <button class="btn btn-secondary w-100 btn-lg mt-3" disabled>
                        <i class="bi bi-x-circle me-2"></i>Fully Booked
                    </button>
                <?php endif; ?>

                <a href="/board-in/pages/search.php" class="btn btn-outline-primary w-100 mt-2">
                    <i class="bi bi-arrow-left me-2"></i>Back to Search
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>