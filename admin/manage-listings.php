<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Manage Listings</h2>
<p>Approve, edit, or remove listings.</p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
