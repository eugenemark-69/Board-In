<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user'])) {
	flash('error', 'You must be logged in to create a listing');
	header('Location: /board-in/user/login.php');
	exit;
}

$title = trim($_POST['title'] ?? '');
$name = trim($_POST['name'] ?? '') ?: $title;
$address = trim($_POST['address'] ?? '');
$description = trim($_POST['description'] ?? '');

$latitude = null;
$longitude = null;
$monthly_rent = !empty($_POST['monthly_rent']) ? floatval($_POST['monthly_rent']) : 0.00;
$security_deposit = !empty($_POST['security_deposit']) ? floatval($_POST['security_deposit']) : 0.00;
$total_rooms = !empty($_POST['total_rooms']) ? intval($_POST['total_rooms']) : 0;
$available_rooms = !empty($_POST['available_rooms']) ? intval($_POST['available_rooms']) : 0;
$gender_allowed = in_array($_POST['gender_allowed'] ?? '', ['male','female','both']) ? $_POST['gender_allowed'] : 'both';
$house_rules = trim($_POST['house_rules'] ?? '');
$curfew_time = !empty($_POST['curfew_time']) ? $_POST['curfew_time'] : null;
$status = in_array($_POST['status'] ?? '', ['available','full','inactive']) ? $_POST['status'] : 'available';

$wifi = isset($_POST['wifi']) ? 1 : 0;
$laundry = isset($_POST['laundry']) ? 1 : 0;
$kitchen = isset($_POST['kitchen']) ? 1 : 0;
$bipsu = isset($_POST['bipsu']) ? 1 : 0;
$own_cr = isset($_POST['own_cr']) ? 1 : 0;
$shared_kitchen = isset($_POST['shared_kitchen']) ? 1 : 0;
$parking = isset($_POST['parking']) ? 1 : 0;
$study_area = isset($_POST['study_area']) ? 1 : 0;
$air_conditioning = isset($_POST['air_conditioning']) ? 1 : 0;
$water_heater = isset($_POST['water_heater']) ? 1 : 0;

if (empty($title)) {
	flash('error', 'Title is required');
	header('Location: /board-in/bh_manager/add-listing.php');
	exit;
}

$manager_id = $_SESSION['user']['id'];

// Geo features removed: latitude/longitude are no longer collected or geocoded

// We'll wrap the boarding_houses + amenities + photos inserts in a DB transaction
$createdFiles = [];
try {
	$conn->begin_transaction();

	// insert into boarding_houses using new schema (keep manager_id for compatibility)
	// Insert listing (latitude/longitude no longer included)
	$cols = ['manager_id','landlord_id','title','name','address','monthly_rent','security_deposit','available_rooms','total_rooms','gender_allowed','description','house_rules','curfew_time','status'];
	$placeholders = array_fill(0, count($cols), '?');
	$bind_types_main = 'iisssddiisssss';
	// Build bind values in correct order
	$bind_values_main = [$manager_id, $manager_id, $title, $name, $address, $monthly_rent, $security_deposit, $available_rooms, $total_rooms, $gender_allowed, $description, $house_rules, $curfew_time, $status];

	$sqlInsert = 'INSERT INTO boarding_houses (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $placeholders) . ')';
	$stmt = $conn->prepare($sqlInsert);
	if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error . ' SQL: ' . $sqlInsert);
	// bind params dynamically
	if ($bind_types_main !== '') {
		$refs = [];
		foreach ($bind_values_main as $k => $v) $refs[$k] = &$bind_values_main[$k];
		array_unshift($refs, $bind_types_main);
		if (!call_user_func_array([$stmt, 'bind_param'], $refs)) {
			throw new Exception('Bind failed: ' . $stmt->error);
		}
	}
	if (!$stmt->execute()) {
		throw new Exception('Execute failed (boarding_houses): ' . $stmt->error);
	}

	$listing_id = $conn->insert_id;

	// create amenities row
	$stmtA = $conn->prepare('INSERT INTO amenities (boarding_house_id, wifi, own_cr, shared_kitchen, laundry_area, parking, study_area, air_conditioning, water_heater) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
	if (!$stmtA) throw new Exception('Prepare failed (amenities): ' . $conn->error);
	if (!$stmtA->bind_param('iiiiiiiii', $listing_id, $wifi, $own_cr, $shared_kitchen, $laundry, $parking, $study_area, $air_conditioning, $water_heater)) {
		throw new Exception('Bind failed (amenities): ' . $stmtA->error);
	}
	if (!$stmtA->execute()) {
		throw new Exception('Execute failed (amenities): ' . $stmtA->error);
	}

	// handle uploads into photos (and legacy images for compatibility)
	if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
		$maxFiles = 6; // limit to 6 images
		$count = min(count($_FILES['images']['name']), $maxFiles);
		$uploadDir = __DIR__ . '/../uploads/' . $listing_id;
		if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
			throw new Exception('Failed to create upload directory');
		}

		$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];

		for ($i = 0; $i < $count; $i++) {
			$name = $_FILES['images']['name'][$i];
			$tmp = $_FILES['images']['tmp_name'][$i];
			$err = $_FILES['images']['error'][$i];
			$size = $_FILES['images']['size'][$i];

			if ($err !== UPLOAD_ERR_OK) continue;
			if ($size > 5 * 1024 * 1024) continue; // max 5MB

			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $tmp);
			finfo_close($finfo);
			if (!isset($allowed[$mime])) continue;

			$ext = $allowed[$mime];
			$basename = bin2hex(random_bytes(8)) . '.' . $ext;
			$target = $uploadDir . DIRECTORY_SEPARATOR . $basename;
			if (move_uploaded_file($tmp, $target)) {
				$createdFiles[] = $target;

				// insert into new photos table
				$stmt2 = $conn->prepare('INSERT INTO photos (boarding_house_id, photo_url, is_primary) VALUES (?, ?, ?)');
				if (!$stmt2) throw new Exception('Prepare failed (photos): ' . $conn->error);
				$is_primary = ($i === 0) ? 1 : 0;
				$url = '/board-in/uploads/' . $listing_id . '/' . $basename;
				if (!$stmt2->bind_param('isi', $listing_id, $url, $is_primary)) {
					throw new Exception('Bind failed (photos): ' . $stmt2->error);
				}
				if (!$stmt2->execute()) {
					throw new Exception('Execute failed (photos): ' . $stmt2->error);
				}

				// also insert into legacy images table for backward compatibility
				$stmt3 = $conn->prepare('INSERT INTO images (listing_id, filename) VALUES (?, ?)');
				if (!$stmt3) throw new Exception('Prepare failed (images): ' . $conn->error);
				if (!$stmt3->bind_param('is', $listing_id, $basename)) {
					throw new Exception('Bind failed (images): ' . $stmt3->error);
				}
				if (!$stmt3->execute()) {
					throw new Exception('Execute failed (images): ' . $stmt3->error);
				}
			}
		}
	}

	$conn->commit();

	flash('success', 'Listing created');
	header('Location: /board-in/bh_manager/my-listings.php');
	exit;

} catch (Exception $e) {
	// rollback DB and cleanup any moved files
	if ($conn->connect_errno === 0) {
		$conn->rollback();
	}
	foreach ($createdFiles as $f) {
		if (file_exists($f)) @unlink($f);
	}
	if (isset($uploadDir) && is_dir($uploadDir)) {
		@rmdir($uploadDir); // only removes if empty
	}
	// Log full error for debugging
	error_log('process-add.php transaction failed: ' . $e->getMessage());

	// Derive a short, user-friendly reason without exposing internals
	$err = $e->getMessage();
	$publicReason = 'An internal error occurred';
	if (stripos($err, 'Failed to create upload directory') !== false) {
		$publicReason = 'Failed to create upload directory (check disk permissions)';
	} elseif (stripos($err, 'Bind failed') !== false) {
		$publicReason = 'Failed to process form data (invalid input)';
	} elseif (stripos($err, 'Execute failed (photos)') !== false || stripos($err, 'Execute failed (images)') !== false) {
		$publicReason = 'Failed to save uploaded images';
	} elseif (stripos($err, 'Execute failed (amenities)') !== false) {
		$publicReason = 'Failed to save listing amenities';
	} elseif (stripos($err, 'Execute failed (boarding_houses)') !== false || stripos($err, 'Prepare failed') !== false) {
		$publicReason = 'Failed to save listing to the database';
	} elseif (stripos($err, 'random_bytes') !== false) {
		$publicReason = 'Failed to generate secure filename for upload';
	} else {
		// Fallback: show a short sanitized excerpt of the error (no newlines, limited length)
		$clean = preg_replace('/[^a-zA-Z0-9 \-_,.:;()\/\\]/', '', $err);
		$clean = preg_replace('/\s+/', ' ', $clean);
		$publicReason = strlen($clean) > 120 ? substr($clean, 0, 117) . '...' : $clean;
		if ($publicReason === '') $publicReason = 'An internal error occurred';
	}

	// If DEBUG is enabled, show full error message to assist local debugging
	if (defined('DEBUG') && DEBUG) {
		// Show full exception (escaped by flash rendering)
		flash('error', 'Failed to create listing: ' . $err);
	} else {
		flash('error', 'Failed to create listing: ' . $publicReason);
	}
	header('Location: /board-in/bh_manager/add-listing.php');
	exit;
}
