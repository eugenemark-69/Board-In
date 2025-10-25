<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in and is a student
if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add favorites']);
    exit;
}

// Fix: Check both role and user_type since your database has both
$userRole = $_SESSION['user']['role'] ?? $_SESSION['user']['user_type'] ?? null;

if ($userRole !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Only students can add favorites']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['listing_id']) || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$listing_id = intval($input['listing_id']);
$action = $input['action'];

// Validate listing exists and is active
$check_stmt = $conn->prepare('SELECT id, status FROM boarding_houses WHERE id = ?');
$check_stmt->bind_param('i', $listing_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Listing not found']);
    exit;
}

$listing = $result->fetch_assoc();

// Only allow favoriting active listings
if (!in_array($listing['status'], ['active', 'available'])) {
    echo json_encode(['success' => false, 'message' => 'This listing is not available']);
    exit;
}

try {
    if ($action === 'add') {
        // Check if already favorited
        $check_fav = $conn->prepare('SELECT id FROM favorites WHERE user_id = ? AND listing_id = ?');
        $check_fav->bind_param('ii', $user_id, $listing_id);
        $check_fav->execute();
        
        if ($check_fav->get_result()->num_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Already in favorites', 'action' => 'exists']);
            exit;
        }
        
        // Add to favorites
        $stmt = $conn->prepare('INSERT INTO favorites (user_id, listing_id, created_at) VALUES (?, ?, NOW())');
        $stmt->bind_param('ii', $user_id, $listing_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Added to favorites',
                'action' => 'added'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add favorite']);
        }
        
    } else if ($action === 'remove') {
        // Remove from favorites
        $stmt = $conn->prepare('DELETE FROM favorites WHERE user_id = ? AND listing_id = ?');
        $stmt->bind_param('ii', $user_id, $listing_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Removed from favorites',
                'action' => 'removed'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove favorite']);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log('Favorite toggle error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>