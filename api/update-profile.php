<?php
session_start();
require_once '../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user']['id'];

// Get and sanitize form data
$full_name = trim($_POST['full_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$contact_number = trim($_POST['contact_number'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$date_of_birth = $_POST['date_of_birth'] ?? null;
$gender = $_POST['gender'] ?? null;
$school = trim($_POST['school'] ?? '');
$student_id = trim($_POST['student_id'] ?? '');
$facebook_url = trim($_POST['facebook_url'] ?? '');
$twitter_url = trim($_POST['twitter_url'] ?? '');
$linkedin_url = trim($_POST['linkedin_url'] ?? '');

// Validate required fields
if (empty($username) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Username and email are required']);
    exit;
}

// Check if username is already taken by another user
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
$stmt->bind_param('si', $username, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username is already taken']);
    exit;
}
$stmt->close();

// Check if email is already taken by another user
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->bind_param('si', $email, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email is already taken']);
    exit;
}
$stmt->close();

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate URLs if provided
if (!empty($facebook_url) && !filter_var($facebook_url, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid Facebook URL']);
    exit;
}

if (!empty($twitter_url) && !filter_var($twitter_url, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid Twitter URL']);
    exit;
}

if (!empty($linkedin_url) && !filter_var($linkedin_url, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid LinkedIn URL']);
    exit;
}

// Prepare update query
$query = "UPDATE users SET 
          full_name = ?, username = ?, email = ?, bio = ?, 
          contact_number = ?, address = ?, date_of_birth = ?, gender = ?,
          school = ?, student_id = ?, facebook_url = ?, twitter_url = ?, linkedin_url = ?,
          updated_at = CURRENT_TIMESTAMP 
          WHERE id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('sssssssssssssi', 
    $full_name, $username, $email, $bio,
    $contact_number, $address, $date_of_birth, $gender,
    $school, $student_id, $facebook_url, $twitter_url, $linkedin_url,
    $user_id
);

if ($stmt->execute()) {
    // Update session data
    $_SESSION['user']['username'] = $username;
    $_SESSION['user']['email'] = $email;
    $_SESSION['user']['full_name'] = $full_name;
    
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile: ' . $conn->error]);
}

$stmt->close();
?>