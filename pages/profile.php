<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// Determine which profile to show
$profile_user_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null);

// If no profile ID and not logged in, redirect to login
if (!$profile_user_id) {
    header('Location: /board-in/user/login.php');
    exit;
}

// Check if viewing own profile
$is_own_profile = isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $profile_user_id;

// Fetch user profile data
$stmt = $conn->prepare("
    SELECT id, username, email, full_name, phone, contact_number, 
           profile_picture, bio, address, date_of_birth, gender, 
           student_id, school, user_type, role, status,
           facebook_url, twitter_url, linkedin_url,
           created_at, last_active
    FROM users 
    WHERE id = ?
");
$stmt->bind_param('i', $profile_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['flash_message'] = 'User not found.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: /board-in/pages/index.php');
    exit;
}

$profile_user = $result->fetch_assoc();
$stmt->close();

// Get statistics
$user_type = $profile_user['user_type'] ?? $profile_user['role'];

// Count listings if landlord
$total_listings = 0;
if ($user_type === 'landlord' || $user_type === 'admin') {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM boarding_houses WHERE user_id = ? AND status != 'rejected'");
    $stmt->bind_param('i', $profile_user_id);
    $stmt->execute();
    $total_listings = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
}

// Count bookings if student
$total_bookings = 0;
if ($user_type === 'student' || $user_type === 'tenant') {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = ?");
    $stmt->bind_param('i', $profile_user_id);
    $stmt->execute();
    $total_bookings = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
}

// Count reviews
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE user_id = ?");
$stmt->bind_param('i', $profile_user_id);
$stmt->execute();
$total_reviews = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Get past posts/activity
$past_posts = [];
if ($user_type === 'landlord' || $user_type === 'admin') {
    // Get boarding house listings
    $stmt = $conn->prepare("
        SELECT id, name, title, address, price, room_type, status, image, created_at, views
        FROM boarding_houses 
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->bind_param('i', $profile_user_id);
    $stmt->execute();
    $past_posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Get recent reviews for students
    $stmt = $conn->prepare("
        SELECT r.*, bh.name as bh_name, bh.title as bh_title
        FROM reviews r
        JOIN boarding_houses bh ON r.bh_id = bh.id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
        LIMIT 10
    ");
    $stmt->bind_param('i', $profile_user_id);
    $stmt->execute();
    $past_posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Default profile picture
$profile_picture = $profile_user['profile_picture'] 
    ? PROFILE_UPLOAD_URL . $profile_user['profile_picture'] 
    : 'https://ui-avatars.com/api/?name=' . urlencode($profile_user['full_name'] ?: $profile_user['username']) . '&size=200&background=667eea&color=fff';

// Calculate time since member joined
$member_since = date('F Y', strtotime($profile_user['created_at']));
$last_active = time_elapsed_string($profile_user['last_active']);

function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d == 0) {
        if ($diff->h == 0) {
            if ($diff->i == 0) {
                return 'Just now';
            }
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        }
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } elseif ($diff->d < 7) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    } elseif ($diff->d < 30) {
        $weeks = floor($diff->d / 7);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } elseif ($diff->m < 12) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    } else {
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    }
}

include '../includes/header.php';
?>

<style>
/* Profile Page Styles */
.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 3rem 2rem;
    color: white;
    margin-bottom: 2rem;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
    position: relative;
    overflow: hidden;
}

.profile-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: pulse 15s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.profile-picture-wrapper {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0 auto 1.5rem;
}

.profile-picture {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 5px solid white;
    object-fit: cover;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease;
}

.profile-picture:hover {
    transform: scale(1.05);
}

.profile-picture-upload {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.profile-picture-upload:hover {
    transform: scale(1.1);
    background: #667eea;
    color: white;
}

.profile-picture-upload input {
    display: none;
}

.profile-name {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-align: center;
}

.profile-username {
    font-size: 1.1rem;
    opacity: 0.9;
    text-align: center;
    margin-bottom: 1rem;
}

.profile-badge {
    display: inline-block;
    padding: 0.5rem 1.5rem;
    border-radius: 50px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.stats-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 2rem;
}

.info-card h4 {
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-card h4 i {
    color: #667eea;
}

.info-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding: 0.75rem;
    border-radius: 10px;
    transition: background 0.2s ease;
}

.info-item:hover {
    background: rgba(102, 126, 234, 0.05);
}

.info-item i {
    color: #667eea;
    margin-right: 1rem;
    font-size: 1.2rem;
    min-width: 24px;
}

.info-item strong {
    min-width: 150px;
    color: #4a5568;
}

.bio-text {
    line-height: 1.8;
    color: #4a5568;
    white-space: pre-wrap;
}

.social-links {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.social-link {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.social-link:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    color: white;
}

.post-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 1.5rem;
}

.post-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.post-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.post-content {
    padding: 1.5rem;
}

.post-title {
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.post-meta {
    color: #6c757d;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.review-rating {
    color: #fbbf24;
}

.btn-edit-profile {
    background: white;
    color: #667eea;
    border: 2px solid white;
    padding: 0.75rem 2rem;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
    text-decoration: none;
}

.btn-edit-profile:hover {
    background: transparent;
    color: white;
    transform: scale(1.05);
}

/* Edit Modal Styles */
.modal-content {
    border-radius: 20px;
    border: none;
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px 20px 0 0;
    padding: 1.5rem 2rem;
}

.modal-header .btn-close {
    filter: brightness(0) invert(1);
}

.form-control, .form-select {
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .profile-header {
        padding: 2rem 1rem;
    }
    
    .profile-name {
        font-size: 1.5rem;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .info-item strong {
        min-width: 100px;
    }
}
</style>

<div class="profile-page">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8 mx-auto text-center">
                    <div class="profile-picture-wrapper">
                        <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile Picture" class="profile-picture" id="profilePicturePreview">
                        <?php if ($is_own_profile): ?>
                            <label class="profile-picture-upload" for="profilePictureInput" title="Change profile picture">
                                <i class="bi bi-camera-fill"></i>
                                <input type="file" id="profilePictureInput" accept="image/*">
                            </label>
                        <?php endif; ?>
                    </div>
                    
                    <h1 class="profile-name"><?= htmlspecialchars($profile_user['full_name'] ?: $profile_user['username']) ?></h1>
                    <p class="profile-username">@<?= htmlspecialchars($profile_user['username']) ?></p>
                    
                    <div class="mb-3">
                        <span class="profile-badge">
                            <i class="bi bi-<?= $user_type === 'landlord' ? 'building' : ($user_type === 'admin' ? 'shield-check' : 'mortarboard') ?> me-2"></i>
                            <?= ucfirst($user_type) ?>
                        </span>
                    </div>
                    
                    <?php if ($is_own_profile): ?>
                        <button class="btn btn-edit-profile" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="bi bi-pencil me-2"></i>Edit Profile
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistics -->
        <div class="row mb-4">
            <?php if ($user_type === 'landlord' || $user_type === 'admin'): ?>
                <div class="col-md-4 mb-3">
                    <div class="stats-card text-center">
                        <div class="stat-number"><?= $total_listings ?></div>
                        <div class="stat-label">Listings</div>
                    </div>
                </div>
            <?php else: ?>
                <div class="col-md-4 mb-3">
                    <div class="stats-card text-center">
                        <div class="stat-number"><?= $total_bookings ?></div>
                        <div class="stat-label">Bookings</div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="col-md-4 mb-3">
                <div class="stats-card text-center">
                    <div class="stat-number"><?= $total_reviews ?></div>
                    <div class="stat-label">Reviews</div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="stats-card text-center">
                    <div class="stat-number">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="stat-label">Member Since</div>
                    <small class="d-block mt-2 text-muted"><?= $member_since ?></small>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Profile Information -->
            <div class="col-lg-4 mb-4">
                <!-- About Section -->
                <div class="info-card">
                    <h4><i class="bi bi-person-lines-fill"></i> About</h4>
                    <?php if ($profile_user['bio']): ?>
                        <p class="bio-text"><?= nl2br(htmlspecialchars($profile_user['bio'])) ?></p>
                    <?php else: ?>
                        <p class="text-muted"><em><?= $is_own_profile ? 'Add a bio to tell others about yourself' : 'No bio available' ?></em></p>
                    <?php endif; ?>
                </div>

                <!-- Contact Information -->
                <div class="info-card">
                    <h4><i class="bi bi-info-circle-fill"></i> Information</h4>
                    
                    <?php if ($profile_user['contact_number'] || $profile_user['phone']): ?>
                        <div class="info-item">
                            <i class="bi bi-telephone-fill"></i>
                            <div>
                                <strong>Phone:</strong><br>
                                <?= htmlspecialchars($profile_user['contact_number'] ?: $profile_user['phone']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($is_own_profile || $user_type === 'landlord'): ?>
                        <div class="info-item">
                            <i class="bi bi-envelope-fill"></i>
                            <div>
                                <strong>Email:</strong><br>
                                <?= htmlspecialchars($profile_user['email']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($profile_user['address']): ?>
                        <div class="info-item">
                            <i class="bi bi-geo-alt-fill"></i>
                            <div>
                                <strong>Address:</strong><br>
                                <?= htmlspecialchars($profile_user['address']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($profile_user['school'] && ($user_type === 'student' || $user_type === 'tenant')): ?>
                        <div class="info-item">
                            <i class="bi bi-building"></i>
                            <div>
                                <strong>School:</strong><br>
                                <?= htmlspecialchars($profile_user['school']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($profile_user['student_id'] && ($user_type === 'student' || $user_type === 'tenant')): ?>
                        <div class="info-item">
                            <i class="bi bi-card-heading"></i>
                            <div>
                                <strong>Student ID:</strong><br>
                                <?= htmlspecialchars($profile_user['student_id']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($profile_user['gender']): ?>
                        <div class="info-item">
                            <i class="bi bi-gender-ambiguous"></i>
                            <div>
                                <strong>Gender:</strong><br>
                                <?= ucfirst(str_replace('_', ' ', htmlspecialchars($profile_user['gender']))) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    
                    <?php if ($profile_user['date_of_birth']): ?>
                        <div class="info-item">
                            <i class="bi bi-calendar-event"></i>
                            <div>
                                <strong>Date of Birth:</strong><br>
                                <?= date('F j, Y', strtotime($profile_user['date_of_birth'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="info-item">
                        <i class="bi bi-clock"></i>
                        <div>
                            <strong>Last Active:</strong><br>
                            <?= $last_active ?>
                        </div>
                    </div>
                    
                    <!-- Social Links -->
                    <?php if ($profile_user['facebook_url'] || $profile_user['twitter_url'] || $profile_user['linkedin_url']): ?>
                        <div class="info-item">
                            <i class="bi bi-share"></i>
                            <div>
                                <strong>Social Media:</strong>
                                <div class="social-links">
                                    <?php if ($profile_user['facebook_url']): ?>
                                        <a href="<?= htmlspecialchars($profile_user['facebook_url']) ?>" target="_blank" class="social-link" title="Facebook">
                                            <i class="bi bi-facebook"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($profile_user['twitter_url']): ?>
                                        <a href="<?= htmlspecialchars($profile_user['twitter_url']) ?>" target="_blank" class="social-link" title="Twitter">
                                            <i class="bi bi-twitter"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($profile_user['linkedin_url']): ?>
                                        <a href="<?= htmlspecialchars($profile_user['linkedin_url']) ?>" target="_blank" class="social-link" title="LinkedIn">
                                            <i class="bi bi-linkedin"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Past Posts/Activity -->
            <div class="col-lg-8">
                <div class="info-card">
                    <h4>
                        <i class="bi bi-<?= $user_type === 'landlord' ? 'house-door' : 'star' ?>-fill"></i>
                        <?= $user_type === 'landlord' || $user_type === 'admin' ? 'Listings' : 'Recent Reviews' ?>
                    </h4>
                    
                    <?php if (empty($past_posts)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 4rem; color: #cbd5e0;"></i>
                            <p class="text-muted mt-3">
                                <?= $user_type === 'landlord' || $user_type === 'admin' 
                                    ? 'No listings yet' 
                                    : 'No reviews yet' ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <?php if ($user_type === 'landlord' || $user_type === 'admin'): ?>
                            <!-- Landlord Listings -->
                            <?php foreach ($past_posts as $post): ?>
                                <div class="post-card">
                                    <div class="row g-0">
                                        <div class="col-md-4">
                                            <?php 
                                            $post_image = $post['image'] 
                                                ? '/board-in/uploads/boarding_houses/' . $post['image'] 
                                                : 'https://via.placeholder.com/400x300?text=No+Image';
                                            ?>
                                            <img src="<?= htmlspecialchars($post_image) ?>" alt="Listing" class="post-image">
                                        </div>
                                        <div class="col-md-8">
                                            <div class="post-content">
                                                <h5 class="post-title"><?= htmlspecialchars($post['title']) ?></h5>
                                                <p class="text-muted mb-2">
                                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($post['address']) ?>
                                                </p>
                                                <div class="post-meta">
                                                    <span>
                                                        <i class="bi bi-currency-peso"></i>
                                                        <?= number_format($post['price'], 2) ?>/month
                                                    </span>
                                                    <span>
                                                        <i class="bi bi-door-open"></i>
                                                        <?= ucfirst($post['room_type']) ?>
                                                    </span>
                                                    <span>
                                                        <i class="bi bi-eye"></i>
                                                        <?= $post['views'] ?> views
                                                    </span>
                                                    <span class="badge bg-<?= $post['status'] === 'active' ? 'success' : ($post['status'] === 'pending' ? 'warning' : 'secondary') ?>">
                                                        <?= ucfirst($post['status']) ?>
                                                    </span>
                                                </div>
                                                <a href="/board-in/pages/listing.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-primary mt-3">
                                                    <i class="bi bi-eye"></i> View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Student Reviews -->
                            <?php foreach ($past_posts as $post): ?>
                                <div class="post-card">
                                    <div class="post-content">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="post-title mb-0"><?= htmlspecialchars($post['bh_title']) ?></h5>
                                            <div class="review-rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="bi bi-star<?= $i <= $post['rating'] ? '-fill' : '' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <p class="text-muted small mb-2">
                                            <i class="bi bi-house-door"></i> <?= htmlspecialchars($post['bh_name']) ?>
                                        </p>
                                        <?php if ($post['comment']): ?>
                                            <p class="mb-2"><?= nl2br(htmlspecialchars($post['comment'])) ?></p>
                                        <?php endif; ?>
                                        <div class="post-meta">
                                            <span>
                                                <i class="bi bi-calendar"></i>
                                                <?= date('M j, Y', strtotime($post['created_at'])) ?>
                                            </span>
                                            <span class="badge bg-<?= $post['status'] === 'approved' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($post['status']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($is_own_profile): ?>
<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Profile
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">
                                <i class="bi bi-person"></i> Full Name
                            </label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?= htmlspecialchars($profile_user['full_name'] ?: '') ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">
                                <i class="bi bi-at"></i> Username
                            </label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= htmlspecialchars($profile_user['username']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bio" class="form-label">
                            <i class="bi bi-card-text"></i> Bio
                        </label>
                        <textarea class="form-control" id="bio" name="bio" rows="4" 
                                  placeholder="Tell us about yourself..."><?= htmlspecialchars($profile_user['bio'] ?: '') ?></textarea>
                        <small class="text-muted">Maximum 500 characters</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_number" class="form-label">
                                <i class="bi bi-telephone"></i> Contact Number
                            </label>
                            <input type="tel" class="form-control" id="contact_number" name="contact_number" 
                                   value="<?= htmlspecialchars($profile_user['contact_number'] ?: $profile_user['phone'] ?: '') ?>"
                                   placeholder="09XX XXX XXXX">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope"></i> Email
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($profile_user['email']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">
                            <i class="bi bi-geo-alt"></i> Address
                        </label>
                        <input type="text" class="form-control" id="address" name="address" 
                               value="<?= htmlspecialchars($profile_user['address'] ?: '') ?>"
                               placeholder="Street, Barangay, City">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_of_birth" class="form-label">
                                <i class="bi bi-calendar"></i> Date of Birth
                            </label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                   value="<?= $profile_user['date_of_birth'] ?: '' ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">
                                <i class="bi bi-gender-ambiguous"></i> Gender
                            </label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Prefer not to say</option>
                                <option value="male" <?= $profile_user['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= $profile_user['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= $profile_user['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
                                <option value="prefer_not_to_say" <?= $profile_user['gender'] === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer not to say</option>
                            </select>
                        </div>
                    </div>
                    
                    <?php if ($user_type === 'student' || $user_type === 'tenant'): ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="school" class="form-label">
                                <i class="bi bi-building"></i> School
                            </label>
                            <input type="text" class="form-control" id="school" name="school" 
                                   value="<?= htmlspecialchars($profile_user['school'] ?: '') ?>"
                                   placeholder="Your school name">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="student_id" class="form-label">
                                <i class="bi bi-card-heading"></i> Student ID
                            </label>
                            <input type="text" class="form-control" id="student_id" name="student_id" 
                                   value="<?= htmlspecialchars($profile_user['student_id'] ?: '') ?>"
                                   placeholder="Your student ID">
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <h6 class="mt-4 mb-3">
                        <i class="bi bi-share"></i> Social Media Links (Optional)
                    </h6>
                    
                    <div class="mb-3">
                        <label for="facebook_url" class="form-label">
                            <i class="bi bi-facebook"></i> Facebook Profile
                        </label>
                        <input type="url" class="form-control" id="facebook_url" name="facebook_url" 
                               value="<?= htmlspecialchars($profile_user['facebook_url'] ?: '') ?>"
                               placeholder="https://facebook.com/yourprofile">
                    </div>
                    
                    <div class="mb-3">
                        <label for="twitter_url" class="form-label">
                            <i class="bi bi-twitter"></i> Twitter Profile
                        </label>
                        <input type="url" class="form-control" id="twitter_url" name="twitter_url" 
                               value="<?= htmlspecialchars($profile_user['twitter_url'] ?: '') ?>"
                               placeholder="https://twitter.com/yourprofile">
                    </div>
                    
                    <div class="mb-3">
                        <label for="linkedin_url" class="form-label">
                            <i class="bi bi-linkedin"></i> LinkedIn Profile
                        </label>
                        <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" 
                               value="<?= htmlspecialchars($profile_user['linkedin_url'] ?: '') ?>"
                               placeholder="https://linkedin.com/in/yourprofile">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveProfileBtn">
                    <i class="bi bi-check-circle"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($is_own_profile): ?>
    // Profile Picture Upload
    const profilePictureInput = document.getElementById('profilePictureInput');
    const profilePicturePreview = document.getElementById('profilePicturePreview');
    
    if (profilePictureInput) {
        profilePictureInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPEG, PNG, GIF, or WebP)');
                    return;
                }
                
                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    return;
                }
                
                // Show loading state
                const uploadBtn = document.querySelector('.profile-picture-upload');
                if (uploadBtn) {
                    uploadBtn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
                    uploadBtn.style.pointerEvents = 'none';
                }
                
                // Preview image
                const reader = new FileReader();
                reader.onload = function(event) {
                    profilePicturePreview.src = event.target.result;
                };
                reader.readAsDataURL(file);
                
                // Upload image
                uploadProfilePicture(file);
            }
        });
    }
    
    function uploadProfilePicture(file) {
        const formData = new FormData();
        formData.append('profile_picture', file);
        formData.append('user_id', '<?= $profile_user_id ?>');
        
        fetch('/board-in/api/upload-profile-picture.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Reset upload button
            const uploadBtn = document.querySelector('.profile-picture-upload');
            if (uploadBtn) {
                uploadBtn.innerHTML = '<i class="bi bi-camera-fill"></i>';
                uploadBtn.style.pointerEvents = 'auto';
            }
            
            if (data.success) {
                showFlashMessage('Profile picture updated successfully!', 'success');
                // Update preview with new image path to prevent caching issues
                if (data.file_path) {
                    profilePicturePreview.src = data.file_path + '?t=' + new Date().getTime();
                }
            } else {
                showFlashMessage('Error: ' + (data.message || 'Failed to upload picture'), 'error');
                // Revert to original image on error
                profilePicturePreview.src = '<?= $profile_picture ?>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Reset upload button
            const uploadBtn = document.querySelector('.profile-picture-upload');
            if (uploadBtn) {
                uploadBtn.innerHTML = '<i class="bi bi-camera-fill"></i>';
                uploadBtn.style.pointerEvents = 'auto';
            }
            showFlashMessage('An error occurred while uploading the picture. Please try again.', 'error');
            // Revert to original image on error
            profilePicturePreview.src = '<?= $profile_picture ?>';
        });
    }
    
    // Save Profile Changes
    const saveProfileBtn = document.getElementById('saveProfileBtn');
    if (saveProfileBtn) {
        saveProfileBtn.addEventListener('click', function() {
            const form = document.getElementById('editProfileForm');
            const formData = new FormData(form);
            formData.append('user_id', '<?= $profile_user_id ?>');
            
            // Disable button to prevent double submission
            saveProfileBtn.disabled = true;
            saveProfileBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
            
            fetch('/board-in/api/update-profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showFlashMessage('Profile updated successfully!', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showFlashMessage('Error: ' + (data.message || 'Failed to update profile'), 'error');
                    saveProfileBtn.disabled = false;
                    saveProfileBtn.innerHTML = '<i class="bi bi-check-circle"></i> Save Changes';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showFlashMessage('An error occurred while updating profile', 'error');
                saveProfileBtn.disabled = false;
                saveProfileBtn.innerHTML = '<i class="bi bi-check-circle"></i> Save Changes';
            });
        });
    }
    
    // Bio character counter
    const bioTextarea = document.getElementById('bio');
    if (bioTextarea) {
        const bioCounter = document.createElement('div');
        bioCounter.className = 'form-text text-end';
        bioCounter.innerHTML = `<span id="bioCount">0</span>/500 characters`;
        bioTextarea.parentNode.appendChild(bioCounter);
        
        bioTextarea.addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('bioCount').textContent = count;
            if (count > 500) {
                this.value = this.value.substring(0, 500);
                document.getElementById('bioCount').textContent = 500;
            }
        });
        
        // Initialize counter
        document.getElementById('bioCount').textContent = bioTextarea.value.length;
    }
    
    // Flash message function
    function showFlashMessage(message, type = 'info') {
        // Remove any existing flash messages
        const existingFlash = document.querySelector('.flash-message');
        if (existingFlash) {
            existingFlash.remove();
        }
        
        const flashDiv = document.createElement('div');
        flashDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show flash-message`;
        flashDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Add styles for fixed positioning
        flashDiv.style.position = 'fixed';
        flashDiv.style.top = '20px';
        flashDiv.style.right = '20px';
        flashDiv.style.zIndex = '9999';
        flashDiv.style.minWidth = '300px';
        
        document.body.appendChild(flashDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (flashDiv.parentNode) {
                flashDiv.remove();
            }
        }, 5000);
    }
    
    // Enhanced form validation
    const editProfileForm = document.getElementById('editProfileForm');
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', function(e) {
            e.preventDefault();
        });
        
        // Username validation
        const usernameInput = document.getElementById('username');
        if (usernameInput) {
            usernameInput.addEventListener('blur', function() {
                const username = this.value.trim();
                if (username.length < 3) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        }
        
        // Email validation
        const emailInput = document.getElementById('email');
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                const email = this.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        }
    }
    <?php endif; ?>
});
</script>

<?php include '../includes/footer.php'; ?>