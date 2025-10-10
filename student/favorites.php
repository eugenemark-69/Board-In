<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['student','admin']);
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Your Favorites</h2>
<p>Saved listings will appear here.</p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
