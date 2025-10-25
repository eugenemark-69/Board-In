<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user']['id'])) {
    $_SESSION['error'] = 'Please login to view your favorites';
    header('Location: /board-in/login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

// Fetch user's favorite listings with all details
$stmt = $conn->prepare('
    SELECT 
        f.id as favorite_id,
        f.created_at as favorited_at,
        bh.*,
        u.full_name as landlord_name,
        u.contact_number as landlord_contact,
        u.profile_picture as landlord_profile_pic,
        (SELECT photo_url FROM photos WHERE boarding_house_id = bh.id ORDER BY is_primary DESC, id ASC LIMIT 1) as main_photo,
        (SELECT COUNT(*) FROM photos WHERE boarding_house_id = bh.id) as photo_count,
        (SELECT AVG(rating) FROM reviews WHERE listing_id = bh.id AND status = "approved") as avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE listing_id = bh.id AND status = "approved") as review_count
    FROM favorites f
    INNER JOIN boarding_houses bh ON f.listing_id = bh.id
    LEFT JOIN users u ON bh.manager_id = u.id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
');

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$favorites = [];
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row;
}
?>

<style>
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --accent-color: #667eea;
    --text-primary: #2d3748;
    --text-secondary: #4a5568;
    --bg-light: #f7fafc;
    --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
    --border-radius: 16px;
}

.favorites-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.page-header {
    text-align: center;
    margin-bottom: 3rem;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.page-subtitle {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.favorites-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
}

.favorite-card {
    background: white;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
}

.favorite-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.card-image {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.favorite-card:hover .card-image img {
    transform: scale(1.1);
}

.image-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(10px);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.remove-favorite-btn {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(10px);
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.remove-favorite-btn:hover {
    background: #ef4444;
    color: white;
    transform: scale(1.1);
}

.card-content {
    padding: 1.5rem;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.card-location {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.card-price {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--accent-color);
    margin-bottom: 1rem;
}

.card-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}

.rating-display {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.rating-stars {
    color: #fbbf24;
}

.availability-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.availability-badge.available {
    background: #d1fae5;
    color: #065f46;
}

.availability-badge.limited {
    background: #fef3c7;
    color: #92400e;
}

.availability-badge.full {
    background: #fee2e2;
    color: #991b1b;
}

.card-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.btn-view, .btn-book {
    padding: 0.75rem;
    border-radius: 10px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-view {
    background: var(--bg-light);
    color: var(--text-primary);
    border: 2px solid #e2e8f0;
}

.btn-view:hover {
    background: white;
    border-color: var(--accent-color);
    color: var(--accent-color);
}

.btn-book {
    background: var(--primary-gradient);
    color: white;
}

.btn-book:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    color: white;
}

.btn-book:disabled {
    background: #cbd5e0;
    cursor: not-allowed;
    transform: none;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    font-size: 5rem;
    color: #cbd5e0;
    margin-bottom: 1.5rem;
}

.empty-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.empty-text {
    color: var(--text-secondary);
    margin-bottom: 2rem;
}

.favorited-date {
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-top: 0.5rem;
}

@media (max-width: 768px) {
    .favorites-grid {
        grid-template-columns: 1fr;
    }
    
    .page-title {
        font-size: 2rem;
    }
}
</style>

<div class="favorites-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="bi bi-heart-fill text-danger me-2"></i>
            My Favorites
        </h1>
        <p class="page-subtitle">
            <?php echo count($favorites); ?> saved <?php echo count($favorites) === 1 ? 'property' : 'properties'; ?>
        </p>
    </div>

    <?php if (empty($favorites)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="bi bi-heart"></i>
            </div>
            <h3 class="empty-title">No favorites yet</h3>
            <p class="empty-text">Start adding boarding houses to your favorites to see them here!</p>
            <a href="/board-in/pages/search.php" class="btn btn-primary btn-lg">
                <i class="bi bi-search me-2"></i>Browse Listings
            </a>
        </div>
    <?php else: ?>
        <div class="favorites-grid">
            <?php foreach ($favorites as $listing): ?>
                <?php
                $main_photo = $listing['main_photo'] ?? 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop';
                $avg_rating = $listing['avg_rating'] ? round($listing['avg_rating'], 1) : 0;
                $review_count = $listing['review_count'] ?? 0;
                
                $available = $listing['available_rooms'];
                $availClass = $available == 0 ? 'full' : ($available <= 3 ? 'limited' : 'available');
                $availText = $available == 0 ? 'Full' : ($available <= 3 ? "$available left" : 'Available');
                ?>
                
                <div class="favorite-card" data-favorite-id="<?php echo $listing['favorite_id']; ?>">
                    <div class="card-image">
                        <img src="<?php echo htmlspecialchars($main_photo); ?>" 
                             alt="<?php echo htmlspecialchars($listing['title']); ?>"
                             loading="lazy">
                        
                        <button class="remove-favorite-btn" 
                                onclick="removeFavorite(<?php echo $listing['id']; ?>, <?php echo $listing['favorite_id']; ?>)"
                                title="Remove from favorites">
                            <i class="bi bi-heart-fill text-danger"></i>
                        </button>
                        
                        <?php if ($listing['photo_count'] > 1): ?>
                            <div class="image-badge">
                                <i class="bi bi-image me-1"></i>
                                <?php echo $listing['photo_count']; ?> photos
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-content">
                        <h3 class="card-title"><?php echo htmlspecialchars($listing['title']); ?></h3>
                        
                        <div class="card-location">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span><?php echo htmlspecialchars($listing['address']); ?></span>
                        </div>

                        <div class="card-price">
                            ₱<?php echo number_format($listing['monthly_rent'], 0); ?>
                            <small class="text-muted" style="font-size: 0.5em;">/month</small>
                        </div>

                        <div class="card-meta">
                            <?php if ($avg_rating > 0): ?>
                                <div class="rating-display">
                                    <div class="rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?php echo $i <= $avg_rating ? '-fill' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="fw-bold"><?php echo $avg_rating; ?></span>
                                    <span class="text-muted">(<?php echo $review_count; ?>)</span>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">No reviews yet</span>
                            <?php endif; ?>

                            <div class="availability-badge <?php echo $availClass; ?>">
                                <?php echo $availText; ?>
                            </div>
                        </div>

                        <div class="favorited-date">
                            <i class="bi bi-clock me-1"></i>
                            Added <?php echo date('M d, Y', strtotime($listing['favorited_at'])); ?>
                        </div>

                        <div class="card-actions mt-3">
                            <a href="/board-in/pages/listing.php?id=<?php echo $listing['id']; ?>" 
                               class="btn-view">
                                <i class="bi bi-eye me-1"></i> View
                            </a>
                            
                            <?php if ($listing['available_rooms'] > 0 && in_array($listing['status'], ['active', 'available'])): ?>
                                <a href="/board-in/student/booking.php?id=<?php echo $listing['id']; ?>" 
                                   class="btn-book">
                                    <i class="bi bi-calendar-check me-1"></i> Book
                                </a>
                            <?php else: ?>
                                <button class="btn-book" disabled>
                                    <i class="bi bi-x-circle me-1"></i> Unavailable
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function removeFavorite(listingId, favoriteId) {
    if (!confirm('Remove this property from your favorites?')) {
        return;
    }

    fetch('/board-in/backend/toggle-favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            listing_id: listingId,
            action: 'remove'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove card from DOM with animation
            const card = document.querySelector(`[data-favorite-id="${favoriteId}"]`);
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.8)';
                
                setTimeout(() => {
                    card.remove();
                    
                    // Check if any favorites left
                    const remainingCards = document.querySelectorAll('.favorite-card');
                    if (remainingCards.length === 0) {
                        location.reload(); // Reload to show empty state
                    } else {
                        // Update count in header
                        const subtitle = document.querySelector('.page-subtitle');
                        if (subtitle) {
                            const count = remainingCards.length;
                            subtitle.textContent = `${count} saved ${count === 1 ? 'property' : 'properties'}`;
                        }
                    }
                }, 300);
            }
            
            // Show toast notification
            showToast('Removed from favorites', 'warning');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error. Please try again.');
    });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : '#f59e0b'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    toast.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>