<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Manage Users</h2>
<p>User management UI.</p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
