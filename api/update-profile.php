<?php
session_start();
require_once '../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user']['id'];

// Check if file was uploaded
if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['profile_picture'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed']);
    exit;
}

// Validate file size (max 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB']);
    exit;
}

// Get file extension
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);

// Generate unique filename
$filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
$upload_path = PROFILE_UPLOAD_DIR . $filename;

// Delete old profile picture if exists
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
if ($result['profile_picture'] && file_exists(PROFILE_UPLOAD_DIR . $result['profile_picture'])) {
    unlink(PROFILE_UPLOAD_DIR . $result['profile_picture']);
}
$stmt->close();

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    exit;
}

// Optimize image (resize if too large)
optimize_image($upload_path, 500, 500);

// Update database
$stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
$stmt->bind_param('si', $filename, $user_id);

if ($stmt->execute()) {
    $_SESSION['user']['profile_picture'] = $filename;
    echo json_encode([
        'success' => true,
        'message' => 'Profile picture updated successfully',
        'file' => $filename,
        'url' => PROFILE_UPLOAD_URL . $filename
    ]);
} else {
    // Delete uploaded file if database update fails
    unlink($upload_path);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();

// Function to optimize and resize image
function optimize_image($file_path, $max_width, $max_height, $quality = 85) {
    list($width, $height, $type) = getimagesize($file_path);
    
    // Skip if image is already smaller than max dimensions
    if ($width <= $max_width && $height <= $max_height) {
        return;
    }
    
    // Calculate new dimensions
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = round($width * $ratio);
    $new_height = round($height * $ratio);
    
    // Create image resource from file
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($file_path);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($file_path);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($file_path);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($file_path);
            break;
        default:
            return;
    }
    
    // Create new image
    $new_image = imagecreatetruecolor($new_width, $new_height);
    
    // Preserve transparency for PNG and GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagecolortransparent($new_image, imagecolorallocatealpha($new_image, 0, 0, 0, 127));
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
    }
    
    // Resize
    imagecopyresampled($new_image, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Save optimized image
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($new_image, $file_path, $quality);
            break;
        case IMAGETYPE_PNG:
            imagepng($new_image, $file_path, floor($quality / 10));
            break;
        case IMAGETYPE_GIF:
            imagegif($new_image, $file_path);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($new_image, $file_path, $quality);
            break;
    }
    
    // Clean up
    imagedestroy($source);
    imagedestroy($new_image);
}
?>