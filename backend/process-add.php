<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDB();
$user_id = $_SESSION['user']['id'] ?? null;

if (!$user_id) {
    flash('error', 'Please login first.');
    header('Location: ../user/login.php');
    exit;
}

// ✅ FIXED: Changed 'role' to 'user_type'
if ($_SESSION['user']['user_type'] !== 'landlord') {
    flash('error', 'Access denied.');
    header('Location: ../index.php');
    exit;
}

$title = trim($_POST['title'] ?? '');
$price = trim($_POST['price'] ?? '');
$location = trim($_POST['location'] ?? '');
$amenities = trim($_POST['amenities'] ?? '');

// ✅ Validate required fields
if (empty($title) || empty($price) || empty($location)) {
    flash('error', 'Please fill in all required fields.');
    header('Location: ../landlord/add-listing.php');
    exit;
}

// ====================
// Upload property image
// ====================
$imagePath = null;
if (!empty($_FILES['bh_photo']['name']) && $_FILES['bh_photo']['error'] === 0) {
    $targetDir = __DIR__ . '/../uploads/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['bh_photo']['name']));
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowedExtensions)) {
        flash('error', 'Invalid image file type. Only JPG and PNG are allowed.');
        header('Location: ../landlord/add-listing.php');
        exit;
    }

    $targetFile = $targetDir . $filename;

    if (move_uploaded_file($_FILES['bh_photo']['tmp_name'], $targetFile)) {
        $imagePath = 'uploads/' . $filename;
    } else {
        flash('error', 'Failed to upload property image.');
        header('Location: ../landlord/add-listing.php');
        exit;
    }
} else {
    flash('error', 'Please upload a property photo.');
    header('Location: ../landlord/add-listing.php');
    exit;
}

// ====================
// Upload DTI Certificate
// ====================
$dtiPath = null;
if (!empty($_FILES['dti_certificate']['name']) && $_FILES['dti_certificate']['error'] === 0) {
    $targetDir = __DIR__ . '/../uploads/dti/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $filename = time() . '_dti_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['dti_certificate']['name']));
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

    if (!in_array($ext, $allowedExtensions)) {
        flash('error', 'Invalid DTI certificate file type. Only JPG, PNG, or PDF are allowed.');
        header('Location: ../landlord/add-listing.php');
        exit;
    }

    $targetFile = $targetDir . $filename;

    if (move_uploaded_file($_FILES['dti_certificate']['tmp_name'], $targetFile)) {
        $dtiPath = 'uploads/dti/' . $filename;
    } else {
        flash('error', 'Failed to upload DTI certificate.');
        header('Location: ../landlord/add-listing.php');
        exit;
    }
} else {
    flash('error', 'Please upload your DTI Certificate.');
    header('Location: ../landlord/add-listing.php');
    exit;
}

// ====================
// Insert into Database
// ====================
try {
    $stmt = $pdo->prepare("
        INSERT INTO listings 
        (user_id, title, description, price, location, amenities, image, dti_certificate, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");

    $result = $stmt->execute([
        $user_id,
        $title,
        '', // description
        $price,
        $location,
        $amenities,
        $imagePath,
        $dtiPath
    ]);

    if ($result) {
        $listing_id = $pdo->lastInsertId();
        flash('success', 'Listing submitted successfully! Listing ID: #' . $listing_id . '. Waiting for admin approval.');
        
        // Redirect to my-listings page
        header('Location: ../landlord/my-listings.php');
        exit;
    } else {
        flash('error', 'Failed to create listing. Please try again.');
        header('Location: ../landlord/add-listing.php');
        exit;
    }
    
} catch (PDOException $e) {
    error_log('Database error in process-add.php: ' . $e->getMessage());
    flash('error', 'Database error: ' . $e->getMessage());
    header('Location: ../landlord/add-listing.php');
    exit;
}
?>