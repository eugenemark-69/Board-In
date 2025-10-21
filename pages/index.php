<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/header.php';

// If user is not logged in, CTAs that lead to searching should direct to sign up
$search_link = isset($_SESSION['user']) ? '/board-in/pages/search.php' : '/board-in/user/register.php';
?>

<style>
/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 70vh;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="white" opacity="0.1"/></svg>') repeat;
    animation: float 20s linear infinite;
}

@keyframes float {
    from { transform: translateY(0); }
    to { transform: translateY(-100px); }
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-section h1 {
    color: white;
    font-size: 3.5rem;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 1.5rem;
    text-shadow: 0 2px 20px rgba(0,0,0,0.2);
}

.hero-section .lead {
    color: rgba(255,255,255,0.95);
    font-size: 1.25rem;
    line-height: 1.8;
}

.hero-img {
    border-radius: 30px !important;
    box-shadow: 0 30px 60px rgba(0,0,0,0.3);
    transform: perspective(1000px) rotateY(-5deg);
    transition: all 0.5s ease;
}

.hero-img:hover {
    transform: perspective(1000px) rotateY(0deg) translateY(-10px);
    box-shadow: 0 40px 80px rgba(0,0,0,0.4);
}

.btn-hero {
    padding: 15px 40px;
    font-size: 1.1rem;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.btn-hero-primary {
    background: white;
    color: #667eea;
    border: none;
}

.btn-hero-primary:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
}

.btn-hero-outline {
    background: transparent;
    color: white;
    border: 2px solid white;
}

.btn-hero-outline:hover {
    background: white;
    color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
}

.trust-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    padding: 10px 20px;
    border-radius: 50px;
    color: white;
    margin-top: 20px;
}

/* Stats Section */
.stats-section {
    margin-top: -80px;
    position: relative;
    z-index: 10;
}

.stat-card {
    background: white;
    border-radius: 20px;
    padding: 40px 30px;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    transition: all 0.4s ease;
    border: none;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.stat-number {
    font-size: 3rem;
    font-weight: 800;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 10px;
    display: block;
}

.stat-label {
    color: #6c757d;
    font-size: 1rem;
    font-weight: 500;
}

/* Features Section */

/* How It Works Section */


/* Sample Listings */


/* CTA Section */


/* Testimonials */

/* Section Headers */
.section-header {
    text-align: center;
    margin-bottom: 60px;
}

.section-header h2 {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 15px;
    position: relative;
    display: inline-block;
}

.section-header h2::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 2px;
}

.section-header p {
    font-size: 1.2rem;
    color: #6c757d;
    margin-top: 25px;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-section h1 {
        font-size: 2rem;
    }
    
    .hero-section {
        min-height: auto;
        padding: 60px 0;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .stats-section {
        margin-top: 40px;
    }
    
    .step-card::after {
        display: none;
    }
    
    .cta-section h2 {
        font-size: 2rem;
    }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeInUp 0.8s ease-out;
}
</style>

<!-- Hero Section -->
<!-- Hero Section -->
<section class="hero-section">
    <div class="container hero-content text-center">
        <div class="animate-fade-in">
            <h1>Find Your Perfect Boarding House in Minutes</h1>
            <p class="lead">Board-In connects BIPSU students with verified, affordable boarding houses near campus. No more endless Facebook scrolling – just reliable listings and real reviews.</p>
            
            <div class="d-flex justify-content-center gap-3 flex-wrap mt-4">
                <a href="<?php echo $search_link; ?>" class="btn btn-hero btn-hero-primary">
                    <i class="bi bi-search me-2"></i>Start Searching
                </a>
                <?php if (!isset($_SESSION['user'])): ?>
                <a href="/board-in/user/register.php" class="btn btn-hero btn-hero-outline">
                    <i class="bi bi-person-plus me-2"></i>Create Account
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>


<!-- Stats Section -->

<!-- Features Section -->

<!-- How It Works Section -->
<section class="how-it-works py-5">
    <div class="container">
        <div class="section-header">
            <h2>How It Works</h2>
            <p>Find your perfect boarding house in three simple steps</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h5 class="fw-bold mb-3">Search & Filter</h5>
                    <p class="text-muted">Browse verified boarding houses near BIPSU. Use filters to find exactly what you're looking for – price, amenities, distance, and more.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h5 class="fw-bold mb-3">Compare & Choose</h5>
                    <p class="text-muted">View photos, read reviews from other students, check locations on the map, and contact landlords directly through the platform.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h5 class="fw-bold mb-3">Book & Move In</h5>
                    <p class="text-muted">Reserve your room securely, manage your booking, and move in with confidence. It's that simple!</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sample Listings -->


<!-- Testimonials Section -->


<!-- CTA Section -->



