<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Login</h2>
<form method="post" action="/board-in/backend/process-login.php">
  <div class="mb-3">
    <label class="form-label">Username</label>
    <input name="username" type="text" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Password</label>
    <input name="password" type="password" class="form-control" required>
  </div>
  <button class="btn btn-primary">Login</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
