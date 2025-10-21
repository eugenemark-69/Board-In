<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
.about-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 0;
    margin-bottom: 60px;
    position: relative;
    overflow: hidden;
}

.about-hero::before {
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

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
}

.stat-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.stat-number {
    font-size: 3rem;
    font-weight: bold;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 10px;
}

.feature-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    margin: 0 auto 20px;
    transition: transform 0.3s ease;
}

.feature-card:hover .feature-icon {
    transform: scale(1.1) rotate(5deg);
}

.feature-card {
    padding: 30px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    height: 100%;
    border: 2px solid transparent;
}

.feature-card:hover {
    border-color: #667eea;
    transform: translateY(-5px);
}

.team-card {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    background: white;
}

.team-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}

.team-avatar {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    margin: 30px auto 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
    font-weight: bold;
}

.section-title {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 50px;
    position: relative;
    display: inline-block;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 2px;
}

.timeline {
    position: relative;
    padding: 50px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 50%;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
}

.timeline-item {
    margin-bottom: 50px;
    position: relative;
}

.timeline-content {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    width: 45%;
}

.timeline-item:nth-child(odd) .timeline-content {
    margin-left: auto;
    margin-right: 55%;
}

.timeline-item:nth-child(even) .timeline-content {
    margin-left: 55%;
}

.timeline-dot {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 20px;
    height: 20px;
    background: white;
    border: 4px solid #667eea;
    border-radius: 50%;
    top: 30px;
}

.cta-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 0;
    border-radius: 30px;
    margin: 60px 0;
    position: relative;
    overflow: hidden;
}

.cta-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 500px;
    height: 500px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
}

@media (max-width: 768px) {
    .timeline::before {
        left: 30px;
    }
    
    .timeline-content,
    .timeline-item:nth-child(odd) .timeline-content,
    .timeline-item:nth-child(even) .timeline-content {
        width: calc(100% - 80px);
        margin-left: 60px !important;
        margin-right: 0 !important;
    }
    
    .timeline-dot {
        left: 30px;
    }
    
    .about-hero {
        padding: 50px 0;
    }
    
    .stat-number {
        font-size: 2rem;
    }
}
</style>

<!-- Hero Section -->
<div class="about-hero">
    <div class="container" style="position: relative; z-index: 1;">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-3 fw-bold mb-4">Making Student Housing Simple</h1>
                <p class="lead fs-4 mb-4">Board-In connects BIPSU students with verified boarding houses in Naval, Biliran. No more endless Facebook scrolling – just reliable listings, real reviews, and secure bookings.</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <!--
                <a href="/browse.php" class="btn btn-light btn-lg px-5 rounded-pill">Browse Listings</a>
                <a href="/landlord/register.php" class="btn btn-outline-light btn-lg px-5 rounded-pill">List Your Property</a>
                 -->
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- Stats Section 
<div class="container">
    
    <div class="row g-4 mb-5" style="margin-top: -60px;">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-number">50+</div>
                <p class="text-muted mb-0">Verified Properties</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-number">200+</div>
                <p class="text-muted mb-0">Happy Students</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-number">4.8★</div>
                <p class="text-muted mb-0">Average Rating</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-number">100%</div>
                <p class="text-muted mb-0">Verified Landlords</p>
            </div>
        </div>
    </div>
    -->

    <!-- Mission Section -->
    <div class="row align-items-center mb-5 py-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <h2 class="section-title">Our Mission</h2>
            <p class="fs-5 text-muted mb-4">We're on a mission to make finding student accommodation as easy as ordering food online. No more sketchy Facebook groups, outdated posts, or unreliable information.</p>
            <p class="fs-5 text-muted">Board-In ensures every listing is verified, every landlord is legitimate, and every student finds a safe place to call home during their studies at BIPSU.</p>
        </div>
        <div class="col-lg-6">
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-4 bg-light rounded-4 text-center">
                        <h3 class="text-primary mb-2">🏠</h3>
                        <p class="fw-bold mb-1">Safe Housing</p>
                        <small class="text-muted">All verified</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-4 bg-light rounded-4 text-center">
                        <h3 class="text-primary mb-2">✓</h3>
                        <p class="fw-bold mb-1">Trusted Reviews</p>
                        <small class="text-muted">Real students</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-4 bg-light rounded-4 text-center">
                        <h3 class="text-primary mb-2">⚡</h3>
                        <p class="fw-bold mb-1">Quick Booking</p>
                        <small class="text-muted">In minutes</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-4 bg-light rounded-4 text-center">
                        <h3 class="text-primary mb-2">💳</h3>
                        <p class="fw-bold mb-1">Secure Payment</p>
                        <small class="text-muted">GCash ready</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="row mb-5 py-5">
        <div class="col-12 text-center mb-5">
            <h2 class="section-title">Why Students Choose Board-In</h2>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-card">
                <div class="feature-icon">🔍</div>
                <h4 class="mb-3">Smart Search</h4>
                <p class="text-muted">Filter by price, location, amenities, and distance from BIPSU. Find exactly what you need in seconds.</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-card">
                <div class="feature-icon">✓</div>
                <h4 class="mb-3">Verified Listings</h4>
                <p class="text-muted">Every property is personally verified. No scams, no fake photos, no surprises when you arrive.</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-card">
                <div class="feature-icon">⭐</div>
                <h4 class="mb-3">Real Reviews</h4>
                <p class="text-muted">Read honest reviews from actual BIPSU students who've lived there. Make informed decisions.</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-card">
                <div class="feature-icon">📍</div>
                <h4 class="mb-3">Location Maps</h4>
                <p class="text-muted">See exact locations and walking distances. Know exactly how far you'll be from campus.</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-card">
                <div class="feature-icon">💬</div>
                <h4 class="mb-3">Direct Contact</h4>
                <p class="text-muted">Message landlords directly through the platform. Get quick responses to all your questions.</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h4 class="mb-3">Mobile Friendly</h4>
                <p class="text-muted">Search and book from your phone, anytime, anywhere. Perfect for busy students on the go.</p>
            </div>
        </div>
    </div>

    <!-- Our Story Timeline -->
    <div class="row mb-5 py-5">
        <div class="col-12 text-center mb-5">
            <h2 class="section-title">Our Story</h2>
            <p class="text-muted fs-5">How Board-In came to life</p>
        </div>
        <div class="col-12">
            <div class="timeline d-none d-md-block">
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h5 class="fw-bold mb-2">The Problem</h5>
                        <p class="text-muted mb-0">As BIPSU students ourselves, we struggled to find decent boarding houses. Facebook groups were chaotic, information was outdated, and we had no way to know if listings were legitimate.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h5 class="fw-bold mb-2">The Idea</h5>
                        <p class="text-muted mb-0">We realized hundreds of students faced the same struggle every semester. There had to be a better way – a dedicated platform built specifically for BIPSU student housing.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h5 class="fw-bold mb-2">Building Board-In</h5>
                        <p class="text-muted mb-0">We spent months talking to students and landlords, understanding their needs, and building a platform that solves real problems for both sides.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h5 class="fw-bold mb-2">Today</h5>
                        <p class="text-muted mb-0">Board-In now helps hundreds of BIPSU students find safe, affordable housing. We're continuously improving based on your feedback to make the experience even better.</p>
                    </div>
                </div>
            </div>
            <!-- Mobile Timeline -->
            <div class="d-md-none">
                <div class="mb-4 p-4 bg-light rounded-4">
                    <h5 class="fw-bold mb-2">The Problem</h5>
                    <p class="text-muted mb-0">As BIPSU students ourselves, we struggled to find decent boarding houses. Facebook groups were chaotic and information was outdated.</p>
                </div>
                <div class="mb-4 p-4 bg-light rounded-4">
                    <h5 class="fw-bold mb-2">The Idea</h5>
                    <p class="text-muted mb-0">We realized hundreds of students faced the same struggle. There had to be a better way – a dedicated platform for BIPSU student housing.</p>
                </div>
                <div class="mb-4 p-4 bg-light rounded-4">
                    <h5 class="fw-bold mb-2">Building Board-In</h5>
                    <p class="text-muted mb-0">We spent months understanding needs from both students and landlords, building a platform that solves real problems.</p>
                </div>
                <div class="mb-4 p-4 bg-light rounded-4">
                    <h5 class="fw-bold mb-2">Today</h5>
                    <p class="text-muted mb-0">Board-In now helps hundreds of BIPSU students find safe housing. We're continuously improving based on your feedback.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Section -->
    <div class="row mb-5 py-5">
        <div class="col-12 text-center mb-5">
            <h2 class="section-title">Meet the Team</h2>
            <p class="text-muted fs-5">The people making it happen</p>
        </div>
        <div class="col-md-4 mb-4">
            <div class="team-card">
                <div class="team-avatar">E</div>
                <div class="card-body text-center pb-4">
                    <h4 class="fw-bold mb-1">Eugene</h4>
                    <p class="text-muted mb-3">Product & UX Design</p>
                    <p class="small">Ensures Board-In is intuitive and enjoyable to use. Former BIPSU student who knows the struggle of finding good boarding houses.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="team-card">
                <div class="team-avatar">R</div>
                <div class="card-body text-center pb-4">
                    <h4 class="fw-bold mb-1">Ritchie</h4>
                    <p class="text-muted mb-3">Backend Developer</p>
                    <p class="small">Builds the tech that powers Board-In. Makes sure everything runs smoothly, from searches to payments to security.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="team-card">
                <div class="team-avatar">L</div>
                <div class="card-body text-center pb-4">
                    <h4 class="fw-bold mb-1">Ladylyn</h4>
                    <p class="text-muted mb-3">PROJECT MANAGER</p>
                    <p class="small">Leads the project, assigns tasks, manages time, and ensures everything stays on track.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="team-card">
                <div class="team-avatar">M</div>
                <div class="card-body text-center pb-4">
                    <h4 class="fw-bold mb-1">MARIEL</h4>
                    <p class="text-muted mb-3">Quality Assurance (QA) / Tester</p>
                    <p class="small">Tests the website for bugs or errors to make sure everything works properly before release.</p>
                </div>
            </div>
        </div>


    <!-- CTA Section -->
     <!--
    <div class="cta-section">
        <div class="container" style="position: relative; z-index: 1;">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold mb-4">Ready to Find Your Perfect Boarding House?</h2>
                    <p class="fs-5 mb-4">Join hundreds of BIPSU students who've already found their home away from home through Board-In.</p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="/browse.php" class="btn btn-light btn-lg px-5 rounded-pill">Start Searching</a>
                        <a href="/student/register.php" class="btn btn-outline-light btn-lg px-5 rounded-pill">Create Account</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
-->
    <!-- Contact Section -->
     <!--
    <div class="row py-5">
        <div class="col-lg-8 mx-auto text-center">
            <h3 class="mb-4">Questions? We're Here to Help</h3>
            <p class="text-muted fs-5 mb-4">Whether you're a student looking for housing or a landlord wanting to list your property, we'd love to hear from you.</p>
            <div class="d-flex gap-4 justify-content-center flex-wrap">
                <div>
                    <p class="text-muted mb-1">Email us at</p>
                    <a href="mailto:hello@board-in.ph" class="fs-5 fw-bold text-decoration-none">hello@board-in.ph</a>
                </div>
                <div>
                    <p class="text-muted mb-1">Call or text</p>
                    <a href="tel:+639123456789" class="fs-5 fw-bold text-decoration-none">+63 912 345 6789</a>
                </div>
            </div>
        </div>
    </div>
</div>
-->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>