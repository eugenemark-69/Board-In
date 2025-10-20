<?php
// header include - call session_start in config/session.php before including
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Board-In - Find Your Perfect Boarding House</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/board-in/assets/css/style.css" rel="stylesheet">
    <style>
      /* Enhanced Navigation Styles */
      .navbar {
        background: white !important;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
        padding: 1rem 0;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
      }
      
      .navbar-brand {
        font-weight: 800;
        font-size: 1.75rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        transition: all 0.3s ease;
        letter-spacing: -0.5px;
      }
      
      .navbar-brand:hover {
        transform: scale(1.05);
        filter: brightness(1.1);
      }
      
      .navbar-toggler {
        border: 2px solid #667eea;
        padding: 0.5rem 0.75rem;
        border-radius: 10px;
        transition: all 0.3s ease;
      }
      
      .navbar-toggler:hover {
        background: rgba(102, 126, 234, 0.1);
        transform: scale(1.05);
      }
      
      .navbar-toggler:focus {
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
      }
      
      .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23667eea' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
      }
      
      .nav-link {
        font-weight: 600;
        font-size: 0.95rem;
        color: #2d3748 !important;
        padding: 0.75rem 1.25rem !important;
        transition: all 0.3s ease;
        position: relative;
        border-radius: 10px;
        margin: 0 0.25rem;
      }
      
      .nav-link::before {
        content: '';
        position: absolute;
        bottom: 8px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 3px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 2px;
        transition: width 0.3s ease;
      }
      
      .nav-link:hover::before {
        width: 60%;
      }
      
      .nav-link:hover {
        color: #667eea !important;
        background: rgba(102, 126, 234, 0.05);
      }
      
      .nav-link i {
        vertical-align: middle;
        font-size: 1.25rem;
      }
      
      /* Dropdown Menu Styling */
      .dropdown-menu {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        padding: 0.75rem 0;
        margin-top: 0.75rem;
        animation: slideDown 0.3s ease;
      }
      
      @keyframes slideDown {
        from {
          opacity: 0;
          transform: translateY(-10px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
      
      .dropdown-item {
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        color: #2d3748;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
      }
      
      .dropdown-item i {
        font-size: 1.1rem;
        width: 24px;
      }
      
      .dropdown-item:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        color: #667eea;
        padding-left: 2rem;
      }
      
      .dropdown-item.text-danger:hover {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
        color: #dc2626;
      }
      
      .dropdown-divider {
        margin: 0.5rem 1rem;
        border-top: 2px solid #e2e8f0;
      }
      
      /* User Menu Icon Enhancement */
      .nav-item.dropdown .nav-link {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0 !important;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
        transition: all 0.3s ease;
      }
      
      .nav-item.dropdown .nav-link::before {
        display: none;
      }
      
      .nav-item.dropdown .nav-link:hover {
        transform: scale(1.1) rotate(90deg);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
      }
      
      .nav-item.dropdown .nav-link i {
        color: white;
        font-size: 1.5rem;
      }
      
      /* Flash Message Styling */
      .alert {
        border-radius: 15px;
        border: none;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        animation: slideInDown 0.5s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 1rem;
      }
      
      @keyframes slideInDown {
        from {
          opacity: 0;
          transform: translateY(-20px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
      
      .alert-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-left: 4px solid #10b981;
      }
      
      .alert-success::before {
        content: '✓';
        font-size: 1.5rem;
        font-weight: bold;
      }
      
      .alert-danger {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        border-left: 4px solid #ef4444;
      }
      
      .alert-danger::before {
        content: '✕';
        font-size: 1.5rem;
        font-weight: bold;
      }
      
      .alert-warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border-left: 4px solid #f59e0b;
      }
      
      .alert-warning::before {
        content: '⚠';
        font-size: 1.5rem;
      }
      
      .alert-info {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
        border-left:
        4px solid #3b82f6;
      }
      
      .alert-info::before {
        content: 'ℹ';
        font-size: 1.5rem;
      }
      
      .alert .btn-close {
        padding: 0.5rem;
        margin-left: auto;
      }
      
      /* Sticky navbar on scroll */
      .navbar.scrolled {
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.12);
      }
      
      /* Main container spacing */
      main.container {
        padding-top: 2rem;
        padding-bottom: 2rem;
      }
      
      /* Mobile Responsive */
      @media (max-width: 991.98px) {
        .navbar-brand {
          font-size: 1.5rem;
        }
        
        .navbar-collapse {
          margin-top: 1rem;
          padding: 1rem;
          background: rgba(102, 126, 234, 0.03);
          border-radius: 15px;
        }
        
        .nav-link {
          margin: 0.25rem 0;
        }
        
        .nav-link::before {
          display: none;
        }
        
        .nav-item.dropdown .nav-link {
          width: 100%;
          border-radius: 10px;
          justify-content: flex-start;
          padding: 0.75rem 1.25rem !important;
        }
        
        .nav-item.dropdown .nav-link:hover {
          transform: none;
        }
        
        .dropdown-menu {
          border-radius: 10px;
          margin-top: 0.5rem;
        }
      }
      
      @media (max-width: 575.98px) {
        .navbar {
          padding: 0.75rem 0;
        }
        
        .navbar-brand {
          font-size: 1.35rem;
        }
        
        main.container {
          padding-top: 1.5rem;
          padding-bottom: 1.5rem;
        }
      }
    </style>
  </head>
  <body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
        <a class="navbar-brand" href="/board-in/pages/index.php">
          <i class="bi bi-house-heart-fill me-1"></i>Board-In
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
          <ul class="navbar-nav ms-auto">
<?php if (isset($_SESSION['user'])): ?>
            <li class="nav-item">
              <a class="nav-link" href="/board-in/pages/search.php">
                <i class="bi bi-search me-1"></i>Browse
              </a>
            </li>
<?php endif; ?>
            <li class="nav-item">
              <a class="nav-link" href="/board-in/pages/about.php">
                <i class="bi bi-info-circle me-1"></i>About
              </a>
            </li>
<?php if (isset($_SESSION['user'])): ?>
            <?php $ut = $_SESSION['user']['user_type'] ?? ($_SESSION['user']['role'] ?? null); if ($ut === 'student'): ?>
              <li class="nav-item">
                <a class="nav-link" href="/board-in/student/booking.php">
                  <i class="bi bi-calendar-check me-1"></i>Bookings
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/board-in/student/favorites.php">
                  <i class="bi bi-heart me-1"></i>Favorites
                </a>
              </li>
            <?php endif; ?>
            <?php if ($ut === 'landlord' || $ut === 'admin'): ?>
              <li class="nav-item">
                <a class="nav-link" href="/board-in/bh_manager/add-listing.php">
                  <i class="bi bi-plus-circle me-1"></i>Add Listing
                </a>
              </li>
            <?php endif; ?>
            <?php if ($ut === 'admin'): ?>
              <li class="nav-item">
                <a class="nav-link" href="/board-in/admin/dashboard.php">
                  <i class="bi bi-speedometer2 me-1"></i>Admin
                </a>
              </li>
            <?php endif; ?>
            <li class="nav-item">
              <a class="nav-link" href="/board-in/pages/index.php">
                <i class="bi bi-house-door me-1"></i>Home
              </a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                <li>
                  <a class="dropdown-item" href="/board-in/pages/profile.php">
                    <i class="bi bi-person me-2"></i>My Profile
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="/board-in/user/settings.php">
                    <i class="bi bi-gear me-2"></i>Settings
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="/board-in/student/notifications.php">
                    <i class="bi bi-bell me-2"></i>Notifications
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="/board-in/student/reviews.php">
                    <i class="bi bi-star me-1"></i>Reviews
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="/board-in/student/favorites.php">
                    <i class="bi bi-heart me-2"></i>Favorites
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item text-danger" href="/board-in/user/logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                  </a>
                </li>
              </ul>
            </li>
<?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="/board-in/pages/index.php">
                <i class="bi bi-house-door me-1"></i>Home
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/board-in/user/login.php">
                <i class="bi bi-box-arrow-in-right me-1"></i>Login
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/board-in/user/register.php">
                <i class="bi bi-person-plus me-1"></i>Register
              </a>
            </li>
<?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>
    
    <main class="container py-4 flex-grow-1">
      <?php if (session_status() === PHP_SESSION_ACTIVE) { require_once __DIR__ . '/functions.php'; flash_render(); } ?>

    <script>
      // Add scrolled class to navbar on scroll
      window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
          navbar.classList.add('scrolled');
        } else {
          navbar.classList.remove('scrolled');
        }
      });
      
      // Auto-hide alerts after 5 seconds
      document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
          setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => alert.remove(), 300);
          }, 5000);
        });
      });
    </script>