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

// Check if file was uploaded
if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$file = $_FILES['profile_picture'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$file_type = mime_content_type($file['tmp_name']);
if (!in_array($file_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Only JPEG, PNG, GIF, and WebP images are allowed']);
    exit;
}

// Validate file size (5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB']);
    exit;
}

// Generate unique filename
$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
$destination = PROFILE_UPLOAD_DIR . $filename;

// Create upload directory if it doesn't exist
if (!file_exists(PROFILE_UPLOAD_DIR)) {
    mkdir(PROFILE_UPLOAD_DIR, 0777, true);
}

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $destination)) {
    // Get old profile picture to delete it later
    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $old_picture = $user['profile_picture'];
    
    // Update database
    $update_stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $update_stmt->bind_param('si', $filename, $user_id);
    
    if ($update_stmt->execute()) {
        // Delete old profile picture if it exists and isn't the default
        if ($old_picture && file_exists(PROFILE_UPLOAD_DIR . $old_picture)) {
            unlink(PROFILE_UPLOAD_DIR . $old_picture);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Profile picture updated successfully',
            'file_path' => PROFILE_UPLOAD_URL . $filename
        ]);
    } else {
        // If database update fails, delete the uploaded file
        unlink($destination);
        echo json_encode(['success' => false, 'message' => 'Failed to update database']);
    }
    
    $update_stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
}
?>