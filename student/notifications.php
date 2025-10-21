<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_role(['student']);
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Notifications</h2>
<p>This is a placeholder for student notifications. You can add push or in-app notification logic here.</p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>