<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user'])) {
    flash('error', 'You must be logged in');
    header('Location: /board-in/user/login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

if (empty($username) || strlen($username) < 3) {
    flash('error', 'Username must be at least 3 characters');
    header('Location: /board-in/user/settings.php');
    exit;
}

// check unique username
$check = $conn->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
$check->bind_param('si', $username, $_SESSION['user']['id']);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    flash('error', 'Username already taken');
    header('Location: /board-in/user/settings.php');
    exit;
}

$updates = [];
$types = '';
$values = [];

$updates[] = 'username = ?'; $types .= 's'; $values[] = $username;

if ($password !== '') {
    if ($password !== $password_confirm) {
        flash('error', 'Passwords do not match');
        header('Location: /board-in/user/settings.php');
        exit;
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $updates[] = 'password = ?'; $types .= 's'; $values[] = $hash;
}

$sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?';
$types .= 'i'; $values[] = $_SESSION['user']['id'];

$stmt = $conn->prepare($sql);
$refs = [];
foreach ($values as $k => $v) $refs[$k] = &$values[$k];
array_unshift($refs, $types);
call_user_func_array([$stmt, 'bind_param'], $refs);
if ($stmt->execute()) {
    // update session username
    $_SESSION['user']['username'] = $username;
    flash('success', 'Settings saved');
    header('Location: /board-in/user/settings.php');
    exit;
}

flash('error', 'Failed to update settings');
header('Location: /board-in/user/settings.php');
exit;
