<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user'])) {
    flash('error', 'You must be logged in to submit verification');
    header('Location: /board-in/user/login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$business_name = trim($_POST['business_name'] ?? '');
$gcash_number = trim($_POST['gcash_number'] ?? '');

$uploadDir = __DIR__ . '/../uploads/landlords/' . $user_id;
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$files = [
    'valid_id' => null,
    'proof_of_ownership' => null,
    'barangay_clearance' => null
];

foreach ($files as $field => $val) {
    if (!empty($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES[$field]['tmp_name'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp);
        finfo_close($finfo);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        if (!isset($allowed[$mime])) continue;
        $ext = $allowed[$mime];
        $basename = $field . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $target = $uploadDir . DIRECTORY_SEPARATOR . $basename;
        if (move_uploaded_file($tmp, $target)) {
            $files[$field] = '/board-in/uploads/landlords/' . $user_id . '/' . $basename;
        }
    }
}

// create or update landlords row
$stmt = $conn->prepare('SELECT id FROM landlords WHERE user_id = ? LIMIT 1');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $lid = $row['id'];
    $stmt2 = $conn->prepare('UPDATE landlords SET business_name = ?, valid_id_url = COALESCE(?, valid_id_url), proof_of_ownership_url = COALESCE(?, proof_of_ownership_url), barangay_clearance_url = COALESCE(?, barangay_clearance_url), gcash_number = ?, verification_status = ? WHERE id = ?');
    $status = 'pending';
    $stmt2->bind_param('ssssssi', $business_name, $files['valid_id'], $files['proof_of_ownership'], $files['barangay_clearance'], $gcash_number, $status, $lid);
    $stmt2->execute();
} else {
    $stmt2 = $conn->prepare('INSERT INTO landlords (user_id, business_name, valid_id_url, proof_of_ownership_url, barangay_clearance_url, verification_status, gcash_number) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $status = 'pending';
    $stmt2->bind_param('issssss', $user_id, $business_name, $files['valid_id'], $files['proof_of_ownership'], $files['barangay_clearance'], $status, $gcash_number);
    $stmt2->execute();
}

flash('success', 'Verification submitted. Admin will review your documents.');
header('Location: /board-in/bh_manager/verification.php');
exit;
