<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
:root {
    --primary-gradient: linear-gradient(135deg, #f63b3bff 0%, #1e40af 100%);
    --secondary-gradient: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);
    --accent-gradient: linear-gradient(135deg, #93c5fd 0%, #3b82f6 100%);
    --dark-gradient: linear-gradient(135deg, #1e3a8a 0%, #3730a3 100%);
}

.about-hero {
    background: var(--primary-gradient);
    color: white;
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

.animated-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.1;
}

.animated-bg::before {
    content: '';
    position: absolute;
    width: 200%;
    height: 200%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>') repeat;
    animation: slide 20s linear infinite;
}

@keyframes slide {
    0% { transform: translate(0, 0); }
    100% { transform: translate(-50%, -50%); }
}

.floating-shapes {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.shape {
    position: absolute;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

.shape:nth-child(1) { width: 80px; height: 80px; top: 10%; left: 10%; animation-delay: 0s; }
.shape:nth-child(2) { width: 120px; height: 120px; top: 60%; left: 80%; animation-delay: -2s; }
.shape:nth-child(3) { width: 60px; height: 60px; top: 80%; left: 20%; animation-delay: -4s; }

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

.hero-content {
    position: relative;
    z-index: 10;
}

.glass-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 2rem;
    margin: 1rem 0;
}

.stat-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem 1.5rem;
    text-align: center;
    box-shadow: 
        0 10px 30px rgba(0,0,0,0.1),
        inset 0 1px 0 rgba(255,255,255,0.6);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: none;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    transition: left 0.6s;
}

.stat-card:hover::before {
    left: 100%;
}

.stat-card:hover {
    transform: translateY(-15px) scale(1.02);
    box-shadow: 
        0 25px 50px rgba(0,0,0,0.15),
        0 0 0 1px rgba(255,255,255,0.8);
}

.stat-number {
    font-size: 5.5rem;
    font-weight: 800;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.parallax-section {
    background: linear-gradient(135deg, #d83860ff 0%, #1ca2f0ff 100%);
    padding: 100px 0;
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
.parallax-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 120%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="white" opacity="0.1"/></svg>') repeat;
    transform: translateZ(0);
}

.feature-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    height: 100%;
    border: 1px solid transparent;
    position: relative;
    overflow: hidden;
}

.feature-card::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--primary-gradient);
    transform: scaleX(0);
    transition: transform 0.4s ease;
}

.feature-card:hover::after {
    transform: scaleX(1);
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 25px 50px rgba(0,0,0,0.15);
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: var(--primary-gradient);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    margin: 0 auto 1.5rem;
    transition: all 0.4s ease;
    transform: rotate(-5deg);
}

.feature-card:hover .feature-icon {
    transform: rotate(0deg) scale(1.1);
    background: var(--secondary-gradient);
}

.team-card {
    background: white;
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: none;
    position: relative;
}

.team-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 8px;
    background: var(--primary-gradient);
    transform: scaleX(0);
    transition: transform 0.4s ease;
}

.team-card:hover::before {
    transform: scaleX(1);
}

.team-card:hover {
    transform: translateY(-15px) scale(1.02);
    box-shadow: 0 30px 60px rgba(0,0,0,0.15);
}

.team-avatar {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    margin: 2rem auto 1.5rem;
    background: var(--primary-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2.5rem;
    font-weight: bold;
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
}

.team-avatar::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    background: var(--secondary-gradient);
    opacity: 0;
    transition: opacity 0.4s ease;
}

.team-card:hover .team-avatar::before {
    opacity: 1;
}

.team-avatar > span {
    position: relative;
    z-index: 2;
}

.tech-stack {
    background: rgba(255,255,255,0.05);
    border-radius: 15px;
    padding: 1rem;
    margin-top: 1rem;
}

.tech-item {
    display: inline-block;
    background: var(--accent-gradient);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    margin: 0.2rem;
    font-weight: 600;
}

.magnetic-btn {
    transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1);
    position: relative;
    overflow: hidden;
}

.magnetic-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    transition: all 0.6s ease;
    transform: translate(-50%, -50%);
}

.magnetic-btn:hover::before {
    width: 300px;
    height: 300px;
}

.scroll-indicator {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
    40% { transform: translateX(-50%) translateY(-10px); }
    60% { transform: translateX(-50%) translateY(-5px); }
}

.gradient-text {
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Advanced animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-on-scroll {
    opacity: 0;
    transform: translateY(50px);
    transition: all 0.8s cubic-bezier(0.215, 0.610, 0.355, 1);
}

.animate-on-scroll.visible {
    opacity: 1;
    transform: translateY(0);
}

/* 3D transform effects */
.perspective-container {
    perspective: 1000px;
}

.rotate-3d {
    transition: transform 0.6s cubic-bezier(0.23, 1, 0.320, 1);
}

.rotate-3d:hover {
    transform: rotateY(10deg) rotateX(5deg) scale(1.02);
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 12px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: var(--primary-gradient);
    border-radius: 10px;
    border: 3px solid #f1f1f1;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--secondary-gradient);
}

/* Loading animation for images */
.lazy-load {
    opacity: 0;
    transform: scale(0.95);
    transition: all 0.6s ease;
}

.lazy-load.loaded {
    opacity: 1;
    transform: scale(1);
}

@media (max-width: 768px) {
    .about-hero {
        min-height: 80vh;
        padding: 100px 0 60px;
    }
    
    .stat-card {
        padding: 2rem 1rem;
    }
    
    .stat-number {
        font-size: 2.5rem;
    }
    
    .feature-card {
        padding: 2rem;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .feature-card,
    .team-card,
    .stat-card {
        background: #2d3748;
        color: white;
    }
}
</style>

<script>
// Intersection Observer for scroll animations
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                
                // Animate stats counting
                if (entry.target.classList.contains('stats-section')) {
                    animateStats();
                }
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });

    // Magnetic button effect
    document.querySelectorAll('.magnetic-btn').forEach(btn => {
        btn.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const angleX = (y - centerY) / 10;
            const angleY = (centerX - x) / 10;
            
            this.style.transform = `perspective(1000px) rotateX(${angleX}deg) rotateY(${angleY}deg) scale(1.05)`;
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
        });
    });

    // Stats counter animation
    function animateStats() {
        const stats = document.querySelectorAll('.stat-number');
        stats.forEach(stat => {
            const target = parseInt(stat.textContent);
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    stat.textContent = target + (stat.textContent.includes('★') ? '★' : '');
                    clearInterval(timer);
                } else {
                    stat.textContent = Math.floor(current) + (stat.textContent.includes('★') ? '★' : '');
                }
            }, 30);
        });
    }
});
</script>

<!-- Enhanced Hero Section -->
<div class="about-hero">
    <div class="animated-bg"></div>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-3 fw-bold mb-4 animate-on-scroll" style="animation-delay: 0.2s;">
                    Making Student Housing <span class="gradient-text">Simple</span>
                </h1>
                <p class="lead fs-4 mb-4 animate-on-scroll glass-card" style="animation-delay: 0.4s;">
                    Board-In connects BIPSU students with verified boarding houses in Naval, Biliran. No more endless Facebook scrolling – just reliable listings, real reviews, and secure bookings.
                </p>
                <div class="d-flex gap-3 justify-content-center flex-wrap animate-on-scroll" style="animation-delay: 0.6s;">
                    <a href="/browse.php" class="btn btn-light btn-lg px-5 rounded-pill magnetic-btn">
                        <span style="position: relative; z-index: 2;">Browse Listings</span>
                    </a>
                    <a href="/landlord/register.php" class="btn btn-outline-light btn-lg px-5 rounded-pill magnetic-btn">
                        <span style="position: relative; z-index: 2;">List Your Property</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="scroll-indicator">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
            <path d="M12 5v14M5 12l7 7 7-7"/>
        </svg>
    </div>
</div>

<div class="container">
    <!-- Enhanced Stats Section -->
    <div class="row g-4 mb-5 stats-section animate-on-scroll" style="margin-top: -60px;">
        <div class="col-md-3 col-6">
            <div class="stat-card perspective-container">
                <div class="rotate-3d">
                    <div class="stat-number">50+</div>
                    <p class="text-muted mb-0">Verified Properties</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card perspective-container">
                <div class="rotate-3d">
                    <div class="stat-number">200+</div>
                    <p class="text-muted mb-0">Happy Students</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card perspective-container">
                <div class="rotate-3d">
                    <div class="stat-number">4.8★</div>
                    <p class="text-muted mb-0">Average Rating</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card perspective-container">
                <div class="rotate-3d">
                    <div class="stat-number">100%</div>
                    <p class="text-muted mb-0">Verified Landlords</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Mission Section with Parallax -->
    <div class="parallax-section rounded-4 mb-5">
        <div class="parallax-bg"></div>
        <div class="container" style="position: relative; z-index: 2;">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0 animate-on-scroll">
                    <h2 class="display-5 fw-bold text-white mb-4">Our Mission</h2>
                    <p class="fs-5 text-light mb-4">We're on a mission to make finding student accommodation as easy as ordering food online. No more sketchy Facebook groups, outdated posts, or unreliable information.</p>
                    <p class="fs-5 text-light">Board-In ensures every listing is verified, every landlord is legitimate, and every student finds a safe place to call home during their studies at BIPSU.</p>
                </div>
                <div class="col-lg-6 animate-on-scroll" style="animation-delay: 0.3s;">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-4 glass-card text-center">
                                <h3 class="text-white mb-2">🏠</h3>
                                <p class="fw-bold mb-1 text-white">Safe Housing</p>
                                <small class="text-light">All verified</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-4 glass-card text-center">
                                <h3 class="text-white mb-2">✓</h3>
                                <p class="fw-bold mb-1 text-white">Trusted Reviews</p>
                                <small class="text-light">Real students</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-4 glass-card text-center">
                                <h3 class="text-white mb-2">⚡</h3>
                                <p class="fw-bold mb-1 text-white">Quick Booking</p>
                                <small class="text-light">In minutes</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-4 glass-card text-center">
                                <h3 class="text-white mb-2">💳</h3>
                                <p class="fw-bold mb-1 text-white">Secure Payment</p>
                                <small class="text-light">GCash ready</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="row mb-5 py-5">
        <div class="col-12 text-center mb-5 animate-on-scroll">
            <h2 class="section-title">Why Students Choose Board-In</h2>
        </div>
        <div class="col-md-4 mb-4 animate-on-scroll">
            <div class="feature-card perspective-container">
                <div class="rotate-3d">
                    <div class="feature-icon">🔍</div>
                    <h4 class="mb-3">Smart Search</h4>
                    <p class="text-muted">Filter by price, location, amenities, and distance from BIPSU. Find exactly what you need in seconds.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4 animate-on-scroll" style="animation-delay: 0.1s;">
            <div class="feature-card perspective-container">
                <div class="rotate-3d">
                    <div class="feature-icon">✓</div>
                    <h4 class="mb-3">Verified Listings</h4>
                    <p class="text-muted">Every property is personally verified. No scams, no fake photos, no surprises when you arrive.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4 animate-on-scroll" style="animation-delay: 0.2s;">
            <div class="feature-card perspective-container">
                <div class="rotate-3d">
                    <div class="feature-icon">⭐</div>
                    <h4 class="mb-3">Real Reviews</h4>
                    <p class="text-muted">Read honest reviews from actual BIPSU students who've lived there. Make informed decisions.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tech Stack Section -->
    <div class="row mb-5 py-5">
        <div class="col-12 text-center mb-5 animate-on-scroll">
            <h2 class="section-title">Built With Modern Tech</h2>
            <p class="text-muted fs-5">Leveraging cutting-edge technologies for the best user experience</p>
        </div>
        <div class="col-12">
            <div class="tech-stack text-center animate-on-scroll">
                <span class="tech-item">PHP 8.1+</span>
                <span class="tech-item">MySQL</span>
                <span class="tech-item">JavaScript ES6+</span>
                <span class="tech-item">Bootstrap 5</span>
                <span class="tech-item">RESTful APIs</span>
                <span class="tech-item">GCash Integration</span>
                <span class="tech-item">Google Maps API</span>
                <span class="tech-item">WebSockets</span>
                <span class="tech-item">JWT Auth</span>
                <span class="tech-item">Mobile-First</span>
            </div>
        </div>
    </div>

    <!-- Enhanced Team Section -->
    <div class="row mb-5 py-5">
        <div class="col-12 text-center mb-5 animate-on-scroll">
            <h2 class="section-title">Meet the Team</h2>
            <p class="text-muted fs-5">The passionate developers behind Board-In</p>
        </div>
        <div class="col-md-4 mb-4 animate-on-scroll">
            <div class="team-card perspective-container">
                <div class="rotate-3d">
                    <div class="team-avatar"><span>E</span></div>
                    <div class="card-body text-center pb-4">
                        <h4 class="fw-bold mb-1">Eugene</h4>
                        <p class="text-muted mb-3">Product & UX Design</p>
                        <p class="small">Ensures Board-In is intuitive and enjoyable to use. Former BIPSU student who knows the struggle of finding good boarding houses.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4 animate-on-scroll" style="animation-delay: 0.1s;">
            <div class="team-card perspective-container">
                <div class="rotate-3d">
                    <div class="team-avatar"><span>R</span></div>
                    <div class="card-body text-center pb-4">
                        <h4 class="fw-bold mb-1">Ritchi</h4>
                        <p class="text-muted mb-3">Backend Developer</p>
                        <p class="small">Builds the tech that powers Board-In. Makes sure everything runs smoothly, from searches to payments to security.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4 animate-on-scroll" style="animation-delay: 0.2s;">
            <div class="team-card perspective-container">
                <div class="rotate-3d">
                    <div class="team-avatar"><span>J</span></div>
                    <div class="card-body text-center pb-4">
                        <h4 class="fw-bold mb-1">Joey Dagrit</h4>
                        <p class="text-muted mb-3">Full Stack Developer</p>
                        <p class="small">Leads the technical vision and implementation. Passionate about creating seamless user experiences with modern web technologies.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced CTA Section -->
    <div class="cta-section rounded-4 mb-5 animate-on-scroll">
        <div class="container" style="position: relative; z-index: 1;">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold mb-4">Ready to Find Your Perfect Boarding House?</h2>
                    <p class="fs-5 mb-4">Join hundreds of BIPSU students who've already found their home away from home through Board-In.</p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="/browse.php" class="btn btn-light btn-lg px-5 rounded-pill magnetic-btn">
                            <span style="position: relative; z-index: 2;">Start Searching</span>
                        </a>
                        <a href="/student/register.php" class="btn btn-outline-light btn-lg px-5 rounded-pill magnetic-btn">
                            <span style="position: relative; z-index: 2;">Create Account</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>