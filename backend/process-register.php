<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

$conn = getDB(); // Get PDO connection

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'student';
$full_name = trim($_POST['full_name'] ?? '');
$contact_number = trim($_POST['contact_number'] ?? '');

// --- VALIDATION ---
if (empty($username) || strlen($username) < 3) {
    $_SESSION['old'] = $_POST;
    flash('error', 'Please provide a username (min 3 characters)');
    header('Location: /board-in/user/register.php');
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
    $_SESSION['old'] = $_POST;
    flash('error', 'Username may contain only letters, numbers, underscore, and dash');
    header('Location: /board-in/user/register.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Please provide a valid email address');
    header('Location: /board-in/user/register.php');
    exit;
}

if (strlen($password) < 6) {
    flash('error', 'Password must be at least 6 characters long');
    header('Location: /board-in/user/register.php');
    exit;
}

// --- CHECK IF USER EXISTS ---
$check = $conn->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
$check->execute([$email, $username]);
if ($check->rowCount() > 0) {
    $_SESSION['old'] = $_POST;
    flash('error', 'Email or username already registered');
    header('Location: /board-in/user/register.php');
    exit;
}

// --- INSERT NEW USER ---
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare('
        INSERT INTO users (email, username, password, user_type, full_name, contact_number)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$email, $username, $hash, $role, $full_name, $contact_number]);

    flash('success', 'Account created successfully! Please log in.');
    header('Location: /board-in/user/login.php');
    exit;

} catch (PDOException $e) {
    flash('error', 'Registration failed: ' . $e->getMessage());
    header('Location: /board-in/user/register.php');
    exit;
}
