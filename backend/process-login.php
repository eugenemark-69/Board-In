<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    flash('error', 'Please provide username and password');
    header('Location: /board-in/user/login.php');
    exit;
}

// Helper: does a column exist in the users table?
function column_exists_users($conn, $col) {
    $c = $conn->real_escape_string($col);
    $r = $conn->query("SHOW COLUMNS FROM users LIKE '{$c}'");
    return $r && $r->num_rows > 0;
}

$user = null;
if (column_exists_users($conn, 'username')) {
    // Try username first (safe because column exists)
    $stmt = $conn->prepare('SELECT id, username, email, password, user_type FROM users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    // If not found, allow fallback to email (user may have typed their email into the same field)
    if (!$user) {
    $stmt = $conn->prepare('SELECT id, username, email, password, user_type FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
    }
} else {
    // username column doesn't exist -> fallback to email-only lookup
    $stmt = $conn->prepare('SELECT id, email, password, user_type FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $u = $res->fetch_assoc();
    if ($u) {
        // normalize to the same shape as when username exists
        $u['username'] = $u['email'];
        $user = $u;
    }
}

if ($user && password_verify($password, $user['password'])) {
    // regenerate session id on login
    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => $user['id'], 'username' => $user['username'] ?? ($user['email'] ?? null), 'email' => $user['email'] ?? null, 'user_type' => $user['user_type'] ?? ($user['role'] ?? null)];
    flash('success', 'Welcome back!');
    header('Location: /board-in/pages/index.php');
    exit;
}
flash('error', 'Invalid credentials');
header('Location: /board-in/user/login.php');
exit;
