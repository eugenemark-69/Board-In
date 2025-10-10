<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'student';
$full_name = trim($_POST['full_name'] ?? '');
$contact_number = trim($_POST['contact_number'] ?? '');

if (empty($username) || strlen($username) < 3) {
    flash('error', 'Please provide a username (min 3 chars)');
    header('Location: /board-in/user/register.php');
    exit;
}
if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
    flash('error', 'Username may contain only letters, numbers, underscore and dash');
    header('Location: /board-in/user/register.php');
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Please provide a valid email');
    header('Location: /board-in/user/register.php');
    exit;
}
// If registering as student, require @bipsu.edu.ph email
if ($role === 'student') {
    if (!preg_match('/@bipsu\.edu\.ph$/i', $email)) {
        flash('error', 'Students must register with a @bipsu.edu.ph email');
        header('Location: /board-in/user/register.php');
        exit;
    }
}
if (strlen($password) < 6) {
    flash('error', 'Password must be at least 6 characters');
    header('Location: /board-in/user/register.php');
    exit;
}

// check existing
$check = $conn->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
$check->bind_param('ss', $email, $username);
$check->execute();
$res = $check->get_result();
if ($res->num_rows > 0) {
    flash('error', 'Email or username already registered');
    header('Location: /board-in/user/register.php');
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (email, username, password, user_type, full_name, contact_number) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->bind_param('ssssss', $email, $username, $hash, $role, $full_name, $contact_number);
if ($stmt->execute()) {
    flash('success', 'Account created. Please log in.');
    header('Location: /board-in/user/login.php');
    exit;
}

flash('error', 'Registration failed. Please try again.');
header('Location: /board-in/user/register.php');
exit;
