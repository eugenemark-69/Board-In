<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
require_login();
?>

<h2>Landlord Dashboard</h2>

<p>Welcome, <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong>!</p>

<div class="d-flex gap-3 my-4">
  <a href="/board-in/landlord/add-listing.php" class="btn btn-success">➕ Add New Listing</a>
  <a href="/board-in/landlord/my-listings.php" class="btn btn-primary">🏠 View My Listings</a>
</div>

<p>Here you can manage your boarding house rooms, update details, and view bookings.</p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
