<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Your Dashboard</h2>
<p>Welcome, <?php echo esc($_SESSION['user']['username'] ?? $_SESSION['user']['email'] ?? 'User'); ?></p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
