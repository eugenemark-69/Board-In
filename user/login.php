<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Login</h2>
<form method="post" action="/board-in/backend/process-login.php">
  <div class="mb-3">
    <label class="form-label">Username or Email</label>
    <input name="username" type="text" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Password</label>
    <input name="password" type="password" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Login as</label>
    <select name="login_as" class="form-select" required>
      <option value="tenant">Student</option>
      <option value="landlord">Landlord</option>
      <option value="admin">Admin</option>
    </select>
  </div>
  <button class="btn btn-primary">Login</button>
</form>



<?php require_once __DIR__ . '/../includes/footer.php'; ?>
