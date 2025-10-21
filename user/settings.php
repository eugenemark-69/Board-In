<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/header.php';

$user = $_SESSION['user'] ?? [];
?>

<h2>Account Settings</h2>
<p>Update your username or change your password here.</p>

<?php if (isset($_SESSION['flash_messages'])) { /* flash_render will show messages */ } ?>

<form method="post" action="/board-in/backend/process-settings.php">
  <div class="mb-3">
    <label class="form-label">Username</label>
    <input name="username" value="<?php echo esc_attr($user['username'] ?? ''); ?>" type="text" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Email (read-only)</label>
    <input value="<?php echo esc_attr($user['email'] ?? ''); ?>" type="email" class="form-control" readonly>
  </div>
  <hr>
  <h5>Change password</h5>
  <div class="mb-3">
    <label class="form-label">New password (leave blank to keep current)</label>
    <input name="password" type="password" class="form-control">
  </div>
  <div class="mb-3">
    <label class="form-label">Confirm new password</label>
    <input name="password_confirm" type="password" class="form-control">
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-primary">Save changes</button>
    <a class="btn btn-outline-secondary" href="/board-in/user/dashboard.php">Cancel</a>
  </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
