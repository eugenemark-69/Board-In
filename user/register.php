<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Register</h2>
<form method="post" action="/board-in/backend/process-register.php">
  <div class="mb-3">
    <label class="form-label">Username</label>
    <input name="username" type="text" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Full name</label>
    <input name="full_name" type="text" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input name="email" type="email" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Contact number</label>
    <input name="contact_number" type="text" class="form-control">
  </div>
  <div class="mb-3">
    <label class="form-label">Password</label>
    <input name="password" type="password" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Role</label>
    <select name="role" class="form-select">
      <option value="student">Student</option>
      <option value="landlord">Landlord</option>
    </select>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-primary">Create account</button>
    <a href="/board-in/user/login.php" class="btn btn-outline-secondary">Login</a>
  </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
