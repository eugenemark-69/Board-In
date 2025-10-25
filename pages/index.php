<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/header.php';

$search_link = isset($_SESSION['user']) ? '/board-in/pages/search.php' : '/board-in/user/register.php';
?>

<style>
/* Hero Section with Advanced Effects */
.hero-section {
    background: linear-gradient(135deg, #cf444bff 0%, #764ba2 100%);
min-height: 100vh;
display: flex;
align-items: center;
position: relative;
overflow: hidden;
width: 100vw;
margin-left: calc(-50vw + 50%);
margin-right: calc(-50vw + 50%);
left: 0;
right: 0;
}

/* Animated gradient background */
.hero-section::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: 
        radial-gradient(circle at 20% 50%, rgba(223, 226, 8, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(7, 111, 172, 0.1) 0%, transparent 50%);
    animation: gradientShift 15s ease infinite;
}

@keyframes gradientShift {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    50% { transform: translate(-5%, -5%) rotate(5deg); }
}

/* Floating particles */
.hero-section::after {
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

/* Gradient orbs */
.gradient-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.3;
    animation: orbFloat 20s ease-in-out infinite;
}

.orb-1 {
    width: 400px;
    height: 400px;
    background: rgba(255, 255, 255, 0.2);
    top: -200px;
    left: -100px;
    animation-delay: 0s;
}

.orb-2 {
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.15);
    bottom: -150px;
    right: -50px;
    animation-delay: 7s;
}

.orb-3 {
    width: 250px;
    height: 250px;
    background: rgba(255, 255, 255, 0.1);
    top: 50%;
    right: 20%;
    animation-delay: 14s;
}

@keyframes orbFloat {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(30px, -30px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
}

.hero-content {
    position: relative;
    z-index: 2;
}

/* Text reveal animation */
.hero-section h1 {
    color: white;
    font-size: 3.5rem;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 1.5rem;
    text-shadow: 0 2px 20px rgba(0,0,0,0.2);
    opacity: 0;
    animation: fadeInUp 1s ease-out 0.2s forwards;
}

/* Staggered word animation */
.hero-section h1 span {
    display: inline-block;
    opacity: 0;
    animation: wordFade 0.8s ease-out forwards;
}

.hero-section h1 span:nth-child(1) { animation-delay: 0.1s; }
.hero-section h1 span:nth-child(2) { animation-delay: 0.2s; }
.hero-section h1 span:nth-child(3) { animation-delay: 0.3s; }
.hero-section h1 span:nth-child(4) { animation-delay: 0.4s; }

@keyframes wordFade {
    from {
        opacity: 0;
        transform: translateY(20px);
        filter: blur(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
        filter: blur(0);
    }
}

.hero-section .lead {
    color: rgba(255,255,255,0.95);
    font-size: 1.25rem;
    line-height: 1.8;
    opacity: 0;
    animation: fadeInUp 1s ease-out 0.6s forwards;
}

/* 3D Image Effect */
.hero-img-wrapper {
    position: relative;
    perspective: 1000px;
    opacity: 0;
    animation: fadeInUp 1s ease-out 0.8s forwards;
}

.hero-img {
    border-radius: 30px !important;
    box-shadow: 
        0 30px 60px rgba(0,0,0,0.3),
        0 0 0 1px rgba(255,255,255,0.1) inset;
    transform: perspective(1000px) rotateY(-5deg);
    transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    position: relative;
}

/* Shimmer effect on image */
.hero-img-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    z-index: 1;
    border-radius: 30px;
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { left: -100%; }
    50%, 100% { left: 100%; }
}

.hero-img-wrapper:hover .hero-img {
    transform: perspective(1000px) rotateY(0deg) translateY(-10px) scale(1.02);
    box-shadow: 
        0 40px 80px rgba(0,0,0,0.4),
        0 0 0 1px rgba(255,255,255,0.2) inset;
}

/* Enhanced Buttons */
.btn-hero {
    padding: 15px 40px;
    font-size: 1.1rem;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    position: relative;
    overflow: hidden;
    opacity: 0;
    animation: fadeInUp 1s ease-out 1s forwards;
}

/* Button ripple effect */
.btn-hero::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-hero:hover::before {
    width: 300px;
    height: 300px;
}

.btn-hero-primary {
    background: white;
    color: #667eea;
    border: none;
}

.btn-hero-primary:hover {
    background: #f8f9fa;
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
    color: #667eea;
}

.btn-hero-outline {
    background: transparent;
    color: white;
    border: 2px solid white;
}

.btn-hero-outline:hover {
    background: white;
    color: #667eea;
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
}

/* Glassmorphism trust badge */
.trust-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    padding: 12px 24px;
    border-radius: 50px;
    color: white;
    margin-top: 20px;
    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    opacity: 0;
    animation: fadeInUp 1s ease-out 1.2s forwards;
    transition: all 0.3s ease;
}

.trust-badge:hover {
    background: rgba(255,255,255,0.25);
    transform: translateY(-2px);
}

/* Floating Stats Section */
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
    transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    border: none;
    height: 100%;
    position: relative;
    overflow: hidden;
}

/* Hover effect background */
.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    opacity: 0;
    transition: opacity 0.4s ease;
    z-index: 0;
}

.stat-card:hover::before {
    opacity: 0.05;
}

.stat-card:hover {
    transform: translateY(-15px) scale(1.02);
    box-shadow: 0 25px 60px rgba(102, 126, 234, 0.2);
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
    position: relative;
    z-index: 1;
    transition: all 0.4s ease;
}

.stat-card:hover .stat-number {
    transform: scale(1.1);
    filter: drop-shadow(0 5px 15px rgba(102, 126, 234, 0.3));
}

.stat-label {
    color: #6c757d;
    font-size: 1rem;
    font-weight: 500;
    position: relative;
    z-index: 1;
}

/* Feature Cards with Advanced Hover */
.feature-card {
    background: white;
    border: 2px solid #f0f0f0;
    border-radius: 20px;
    padding: 40px 30px;
    transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    height: 100%;
    position: relative;
    overflow: hidden;
}

/* Top gradient bar */
.feature-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 5px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transform: scaleX(0);
    transition: transform 0.4s cubic-bezier(0.23, 1, 0.32, 1);
}

/* Glow effect */
.feature-card::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
    z-index: 0;
}

.feature-card:hover::before {
    transform: scaleX(1);
}

.feature-card:hover::after {
    width: 500px;
    height: 500px;
}

.feature-card:hover {
    border-color: #667eea;
    transform: translateY(-15px);
    box-shadow: 0 20px 50px rgba(102, 126, 234, 0.2);
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    margin: 0 auto 20px;
    transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    position: relative;
    z-index: 1;
}

/* Icon pulse effect */
.feature-card:hover .feature-icon {
    transform: scale(1.15) rotate(5deg);
    box-shadow: 
        0 10px 30px rgba(102, 126, 234, 0.4),
        0 0 0 10px rgba(102, 126, 234, 0.1);
}

/* How It Works with Connection Lines */
.how-it-works {
    background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
    position: relative;
}

.step-card {
    background: white;
    border-radius: 20px;
    padding: 40px 30px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    height: 100%;
    position: relative;
}

/* Animated arrow */
.step-card::after {
    content: '→';
    position: absolute;
    right: -30px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 2rem;
    color: #667eea;
    opacity: 0.3;
    transition: all 0.3s ease;
}

.step-card:hover::after {
    opacity: 0.8;
    right: -35px;
}

.step-card:last-child::after {
    display: none;
}

.step-number {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    font-weight: bold;
    margin: 0 auto 20px;
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
    transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
}

.step-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.12);
}

.step-card:hover .step-number {
    transform: scale(1.15) rotate(360deg);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.5);
}

/* Listing Cards with Image Zoom */
.listing-card {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    height: 100%;
}

.listing-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}

.listing-card .position-relative {
    overflow: hidden;
}

.listing-card img {
    height: 250px;
    object-fit: cover;
    transition: transform 0.6s cubic-bezier(0.23, 1, 0.32, 1);
}

.listing-card:hover img {
    transform: scale(1.15) rotate(2deg);
}

.listing-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(10px);
    padding: 8px 15px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.9rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

.listing-card:hover .listing-badge {
    transform: scale(1.1);
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}

.listing-price {
    color: #667eea;
    font-size: 1.5rem;
    font-weight: 700;
}

.listing-location {
    color: #6c757d;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* CTA Section with Animated Background */
.cta-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 100px 0;
    position: relative;
    overflow: hidden;
}

/* Animated orbs */
.cta-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 600px;
    height: 600px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    animation: ctaOrb1 20s ease-in-out infinite;
}

.cta-section::after {
    content: '';
    position: absolute;
    bottom: -50%;
    left: -10%;
    width: 500px;
    height: 500px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
    animation: ctaOrb2 25s ease-in-out infinite;
}

@keyframes ctaOrb1 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(-50px, 50px) scale(1.2); }
}

@keyframes ctaOrb2 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(50px, -50px) scale(1.1); }
}

.cta-content {
    position: relative;
    z-index: 2;
}

.cta-section h2 {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 20px;
    text-shadow: 0 5px 20px rgba(0,0,0,0.2);
}

/* Testimonials with Quote Animation */
.testimonial-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    height: 100%;
    position: relative;
    transition: all 0.4s ease;
}

.testimonial-card::before {
    content: '"';
    position: absolute;
    top: 20px;
    left: 30px;
    font-size: 5rem;
    color: #667eea;
    opacity: 0.1;
    font-family: Georgia, serif;
    transition: all 0.4s ease;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.12);
}

.testimonial-card:hover::before {
    opacity: 0.2;
    transform: scale(1.1);
}

.testimonial-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.5rem;
    transition: all 0.3s ease;
}

.testimonial-card:hover .testimonial-avatar {
    transform: scale(1.1);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
}

.rating {
    color: #ffc107;
}

/* Section Headers with Line Animation */
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
    animation: lineExpand 1s ease-out;
}

@keyframes lineExpand {
    from { width: 0; }
    to { width: 80px; }
}

.section-header p {
    font-size: 1.2rem;
    color: #6c757d;
    margin-top: 25px;
}

/* Scroll animations */
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

.animate-on-scroll {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.8s ease-out, transform 0.8s ease-out;
}

.animate-on-scroll.visible {
    opacity: 1;
    transform: translateY(0);
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

    .gradient-orb {
        display: none;
    }
}
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="gradient-orb orb-1"></div>
    <div class="gradient-orb orb-2"></div>
    <div class="gradient-orb orb-3"></div>
    
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div>
                    <h1>
                        <span>Find</span> <span>Your</span> <span>Perfect</span> <span>Boarding House</span> in Minutes
                    </h1>
                    <p class="lead">Board-In connects BIPSU students with verified, affordable boarding houses near campus. No more endless Facebook scrolling — just reliable listings and real reviews.</p>
                    
                    <div class="d-flex gap-3 flex-wrap mt-4">
                        <a href="<?php echo $search_link; ?>" class="btn btn-hero btn-hero-primary">
                            <i class="bi bi-search me-2"></i>Start Searching
                        </a>
                        <?php if (!isset($_SESSION['user'])): ?>
                        <a href="/board-in/user/register.php" class="btn btn-hero btn-hero-outline">
                            <i class="bi bi-person-plus me-2"></i>Create Account
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="trust-badge">
                        <i class="bi bi-shield-check fs-4"></i>
                        <span>100% Verified Listings • Trusted by 200+ Students</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 d-none d-lg-block">
                <?php
                $preferred = __DIR__ . '/../assets/images/Bishemar-Apartelle-4.jpg';
                if (file_exists($preferred)) {
                    $hero_local = '/board-in/assets/images/Bishemar-Apartelle-4.jpg';
                } else {
                    $files = glob(__DIR__ . '/../assets/images/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
                    if ($files && count($files) > 0) {
                        $picked = $files[array_rand($files)];
                        $hero_local = '/board-in/assets/images/' . basename($picked);
                    } else {
                        $hero_local = '/board-in/assets/images/hero.jpg';
                    }
                }
                $hero_fallback = 'https://source.unsplash.com/1200x800/?boarding-house,student';
                ?>
                <div class="hero-img-wrapper">
                    <img src="<?php echo $hero_local; ?>" alt="Boarding house" class="img-fluid hero-img" onerror="this.onerror=null;this.src='<?php echo $hero_fallback; ?>'">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <span class="stat-number">50+</span>
                    <div class="stat-label">Verified Properties</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <span class="stat-number">200+</span>
                    <div class="stat-label">Happy Students</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <span class="stat-number">4.8★</span>
                    <div class="stat-label">Average Rating</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <span class="stat-number">24/7</span>
                    <div class="stat-label">Support Available</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 my-5">
    <div class="container">
        <div class="section-header">
            <h2>Why Students Love Board-In</h2>
            <p>Everything you need to find your perfect home away from home</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4 class="mb-3 text-center">100% Verified</h4>
                    <p class="text-muted text-center">Every boarding house is personally verified by our team. No scams, no fake photos, just real homes.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <h4 class="mb-3 text-center">Smart Search</h4>
                    <p class="text-muted text-center">Filter by price, location, amenities, and distance from BIPSU. Find exactly what you need in seconds.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <h4 class="mb-3 text-center">Real Reviews</h4>
                    <p class="text-muted text-center">Read honest reviews from verified BIPSU students. Make informed decisions based on real experiences.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <h4 class="mb-3 text-center">Location Maps</h4>
                    <p class="text-muted text-center">See exact locations and walking distances. Know exactly how far you'll be from campus.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h4 class="mb-3 text-center">Easy Booking</h4>
                    <p class="text-muted text-center">Book in minutes with our simple process. Manage everything from your dashboard.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-phone"></i>
                    </div>
                    <h4 class="mb-3 text-center">Mobile Friendly</h4>
                    <p class="text-muted text-center">Search and book from your phone, anytime, anywhere. Perfect for busy students on the go.</p>
                </div>
            </div>
        </div>
    </div>
</section>

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
                    <p class="text-muted">Browse verified boarding houses near BIPSU. Use filters to find exactly what you're looking for — price, amenities, distance, and more.</p>
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
<section class="py-5 my-5">
    <div class="container">
        <div class="section-header">
            <h2>Featured Boarding Houses</h2>
            <p>Check out some of our most popular listings near BIPSU</p>
        </div>

        <div class="row g-4">
            <?php
            $localImages = [
                '/board-in/assets/images/497806866.jpg',
                '/board-in/assets/images/Chilton-Cantelo-School_008.jpg',
                '/board-in/assets/images/Chonas-Boarding-House-2.jpg',
                '/board-in/assets/images/images (2).jpg',
            ];
            $titles = [
                'Cozy Room near Campus',
                'Modern Shared Living',
                'Studio with Private Bath',
                'Budget-Friendly Dorm',
            ];
            $prices = ['₱4,500','₱3,200','₱6,800','₱5,500'];
            $locations = ['5 min walk to BIPSU', '10 min walk to BIPSU', '3 min walk to BIPSU', '8 min walk to BIPSU'];
            $badges = ['Popular', 'New', 'Premium', 'Best Value'];
            
            for ($i = 0; $i < count($titles); $i++):
                $local = $localImages[$i];
                $fallback = 'https://source.unsplash.com/600x400/?boarding-house';
            ?>
            <div class="col-sm-6 col-lg-3">
                <div class="listing-card card">
                    <div class="position-relative overflow-hidden">
                        <img src="<?php echo $local; ?>" onerror="this.onerror=null;this.src='<?php echo $fallback; ?>'" class="card-img-top" alt="<?php echo htmlspecialchars($titles[$i]); ?>">
                        <span class="listing-badge"><?php echo $badges[$i]; ?></span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title mb-2"><?php echo htmlspecialchars($titles[$i]); ?></h5>
                        <p class="listing-location mb-3">
                            <i class="bi bi-geo-alt-fill"></i>
                            <?php echo $locations[$i]; ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="listing-price"><?php echo $prices[$i]; ?>/mo</span>
                            <a href="<?php echo $search_link; ?>" class="btn btn-sm btn-outline-primary rounded-pill">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endfor; ?>
        </div>

        <div class="text-center mt-5">
            <a href="<?php echo $search_link; ?>" class="btn btn-primary btn-lg px-5 rounded-pill">
                View All Listings <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 my-5 bg-light">
    <div class="container">
        <div class="section-header">
            <h2>What Students Say</h2>
            <p>Real experiences from BIPSU students who found their home through Board-In</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="rating mb-3">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="mb-4">"Board-In made finding a boarding house so easy! I found my place in just 2 days. The reviews from other students really helped me decide."</p>
                    <div class="d-flex align-items-center">
                        <div class="testimonial-avatar me-3">M</div>
                        <div>
                            <div class="fw-bold">Maria Santos</div>
                            <small class="text-muted">BS Education Student</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="rating mb-3">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="mb-4">"No more dealing with outdated Facebook posts! Everything here is verified and up-to-date. The map feature is super helpful too."</p>
                    <div class="d-flex align-items-center">
                        <div class="testimonial-avatar me-3">J</div>
                        <div>
                            <div class="fw-bold">Juan Dela Cruz</div>
                            <small class="text-muted">BS Engineering Student</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="rating mb-3">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="mb-4">"As a landlord, Board-In helps me reach serious students easily. The verification process ensures quality tenants. Highly recommend!"</p>
                    <div class="d-flex align-items-center">
                        <div class="testimonial-avatar me-3">A</div>
                        <div>
                            <div class="fw-bold">Anna Reyes</div>
                            <small class="text-muted">Boarding House Owner</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center cta-content">
                <h2 class="mb-4">Ready to Find Your New Home?</h2>
                <p class="fs-5 mb-5">Join hundreds of BIPSU students who've already found their perfect boarding house through Board-In. Start your search today — it's free!</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="<?php echo $search_link; ?>" class="btn btn-hero btn-hero-primary">
                        <i class="bi bi-search me-2"></i>Browse Listings
                    </a>
                    <?php if (!isset($_SESSION['user'])): ?>
                    <a href="/board-in/user/register.php" class="btn btn-hero btn-hero-outline">
                        <i class="bi bi-person-plus me-2"></i>Sign Up Free
                    </a>
                    <?php endif; ?>
                </div>
                <p class="mt-4 small">
                    Already have an account? <a href="/board-in/user/login.php" class="text-white fw-bold text-decoration-underline">Log in here</a>
                </p>
            </div>
        </div>
    </div>
</section>

<script>
// Intersection Observer for scroll animations
document.addEventListener('DOMContentLoaded', function() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    // Add animation class to sections
    const sections = document.querySelectorAll('.feature-card, .step-card, .listing-card, .testimonial-card, .stat-card');
    sections.forEach(section => {
        section.classList.add('animate-on-scroll');
        observer.observe(section);
    });

    // Mouse parallax effect on hero image
    const heroImg = document.querySelector('.hero-img');
    if (heroImg && window.innerWidth > 768) {
        document.querySelector('.hero-section').addEventListener('mousemove', function(e) {
            const x = (e.clientX / window.innerWidth - 0.5) * 20;
            const y = (e.clientY / window.innerHeight - 0.5) * 20;
            heroImg.style.transform = `perspective(1000px) rotateY(${-5 + x}deg) rotateX(${y}deg)`;
        });
    }

    // Magnetic button effect
    const buttons = document.querySelectorAll('.btn-hero');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function(e) {
            button.style.transition = 'transform 0.1s ease';
        });
        
        button.addEventListener('mousemove', function(e) {
            const rect = button.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            
            button.style.transform = `translate(${x * 0.1}px, ${y * 0.1}px) scale(1.02)`;
        });
        
        button.addEventListener('mouseleave', function() {
            button.style.transition = 'transform 0.3s cubic-bezier(0.23, 1, 0.32, 1)';
            button.style.transform = 'translate(0, 0) scale(1)';
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>