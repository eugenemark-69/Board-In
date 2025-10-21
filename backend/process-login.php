<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$pdo = getDB();

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$login_as = strtolower(trim($_POST['login_as'] ?? ''));

if (empty($username) || empty($password)) {
    flash('error', 'Please provide username and password.');
    header('Location: /board-in/user/login.php');
    exit;
}

try {
    // ✅ Choose table based on selected role
    switch ($login_as) {
        case 'student':
        case 'tenant':
        case 'landlord': // ✅ landlords also stored in users table
        $table = 'users';
        break;
        case 'admin':
        $table = 'admins';
        break;
       default:
        flash('error', 'Invalid account type.');
        header('Location: /board-in/user/login.php');
        exit;
    }


    // ✅ Query that table
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE username = :username OR email = :email LIMIT 1");
    $stmt->execute(['username' => $username, 'email' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        flash('error', 'User not found.');
        header('Location: /board-in/user/login.php');
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        flash('error', 'Incorrect password.');
        header('Location: /board-in/user/login.php');
        exit;
    }

    // ✅ Normalize and store session
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'user_type' => $login_as
    ];

    flash('success', 'Welcome back, ' . htmlspecialchars($user['username']) . '!');

    // ✅ Redirect based on role
    switch ($login_as) {
        case 'landlord':
            header('Location: /board-in/landlord/dashboard.php');
            break;
        case 'student':
        case 'tenant':
            header('Location: /board-in/user/dashboard.php');
            break;
        case 'admin':
            header('Location: /board-in/admin/dashboard.php');
            break;
    }
    exit;

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    flash('error', 'Database error. Please try again later.');
    header('Location: /board-in/user/login.php');
    exit;
}
