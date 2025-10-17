<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Require landlord or admin role
require_role(['landlord', 'admin']);

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /board-in/bh_manager/add-listing.php');
    exit;
}

$user_id = $_SESSION['user']['id'] ?? null;

// Validate required fields
$required_fields = ['title', 'room_type'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error'] = "Required field missing: " . ucfirst(str_replace('_', ' ', $field));
        header('Location: /board-in/pages/add-listing.php');
        exit;
    }
}

// Sanitize and prepare form data
$title = $conn->real_escape_string(trim($_POST['title']));
$name = !empty($_POST['name']) ? $conn->real_escape_string(trim($_POST['name'])) : $title;
$address = !empty($_POST['address']) ? $conn->real_escape_string(trim($_POST['address'])) : '';
$city = !empty($_POST['city']) ? $conn->real_escape_string(trim($_POST['city'])) : '';
$contact_phone = !empty($_POST['contact_phone']) ? $conn->real_escape_string(trim($_POST['contact_phone'])) : '';

// Financial fields
$monthly_rent = !empty($_POST['monthly_rent']) ? floatval($_POST['monthly_rent']) : 0.00;
$security_deposit = !empty($_POST['security_deposit']) ? floatval($_POST['security_deposit']) : 0.00;

// Room details
$room_type = $conn->real_escape_string($_POST['room_type']);
$available_rooms = !empty($_POST['available_rooms']) ? intval($_POST['available_rooms']) : 1;
$total_rooms = !empty($_POST['total_rooms']) ? intval($_POST['total_rooms']) : $available_rooms;
$gender_allowed = !empty($_POST['gender_allowed']) ? $conn->real_escape_string($_POST['gender_allowed']) : 'both';

// Optional fields
$description = !empty($_POST['description']) ? $conn->real_escape_string(trim($_POST['description'])) : '';
$house_rules = !empty($_POST['house_rules']) ? $conn->real_escape_string(trim($_POST['house_rules'])) : '';
$curfew_time = !empty($_POST['curfew_time']) ? $conn->real_escape_string($_POST['curfew_time']) : NULL;
$status = !empty($_POST['status']) ? $conn->real_escape_string($_POST['status']) : 'available';

// Amenities (checkboxes)
$wifi = isset($_POST['wifi']) ? 1 : 0;
$own_cr = isset($_POST['own_cr']) ? 1 : 0;
$shared_kitchen = isset($_POST['shared_kitchen']) ? 1 : 0;
$laundry = isset($_POST['laundry']) ? 1 : 0;
$parking = isset($_POST['parking']) ? 1 : 0;
$study_area = isset($_POST['study_area']) ? 1 : 0;
$air_conditioning = isset($_POST['air_conditioning']) ? 1 : 0;
$water_heater = isset($_POST['water_heater']) ? 1 : 0;
$close_to_bipsu = isset($_POST['bipsu']) ? 1 : 0;

// Set default values for compatibility with existing schema
$province = 'Biliran'; // Default province
$location = $city; // Use city as location

// Start transaction
$conn->begin_transaction();

try {
    // Insert into boarding_houses table (without location field to avoid conflicts)
    $sql = "INSERT INTO boarding_houses (
        user_id, 
        landlord_id,
        title, 
        name, 
        address, 
        city, 
        province,
        contact_phone,
        monthly_rent, 
        security_deposit, 
        price,
        room_type, 
        available_rooms, 
        total_rooms, 
        gender_allowed, 
        description, 
        house_rules, 
        curfew_time, 
        status,
        wifi,
        parking,
        laundry,
        aircon,
        water_heater,
        close_to_bipsu,
        created_at
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
    )";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    // Bind parameters
    $landlord_id = $user_id; // same for now

$stmt->bind_param(
    "iissssssdddsiisssssiiiiii",
    $user_id,
    $landlord_id,
    $title,
    $name,
    $address,
    $city,
    $province,
    $contact_phone,
    $monthly_rent,
    $security_deposit,
    $monthly_rent,     // price
    $room_type,
    $available_rooms,
    $total_rooms,
    $gender_allowed,
    $description,
    $house_rules,
    $curfew_time,
    $status,
    $wifi,
    $parking,
    $laundry,
    $air_conditioning,
    $water_heater,
    $close_to_bipsu
);

    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $boarding_house_id = $conn->insert_id;
    $stmt->close();
    
    // Insert into amenities table
    $sql_amenities = "INSERT INTO amenities (
        boarding_house_id,
        wifi,
        own_cr,
        shared_kitchen,
        laundry_area,
        parking,
        study_area,
        air_conditioning,
        water_heater
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_amenities = $conn->prepare($sql_amenities);
    
    if (!$stmt_amenities) {
        throw new Exception("Amenities prepare failed: " . $conn->error);
    }
    
    $stmt_amenities->bind_param(
        "iiiiiiiii",
        $boarding_house_id,
        $wifi,
        $own_cr,
        $shared_kitchen,
        $laundry,
        $parking,
        $study_area,
        $air_conditioning,
        $water_heater
    );
    
    if (!$stmt_amenities->execute()) {
        throw new Exception("Amenities execute failed: " . $stmt_amenities->error);
    }
    
    $stmt_amenities->close();
    
    // Handle image uploads
    if (!empty($_FILES['images']['name'][0])) {
        $upload_dir = __DIR__ . '/../uploads/listings/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_file_size = 5 * 1024 * 1024; // 5MB
        $max_images = 10;
        
        $uploaded_count = 0;
        $is_first_image = true;
        
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($uploaded_count >= $max_images) {
                break;
            }
            
            if (empty($tmp_name) || $_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            // Validate file type
            $file_type = $_FILES['images']['type'][$key];
            if (!in_array($file_type, $allowed_types)) {
                continue;
            }
            
            // Validate file size
            if ($_FILES['images']['size'][$key] > $max_file_size) {
                continue;
            }
            
            // Generate unique filename
            $extension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
            $filename = 'listing_' . $boarding_house_id . '_' . time() . '_' . $uploaded_count . '.' . $extension;
            $filepath = $upload_dir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($tmp_name, $filepath)) {
                $image_path = '/board-in/uploads/listings/' . $filename;
                $is_primary = $is_first_image ? 1 : 0;
                
                // Try to insert into images table (with listing_id column)
                try {
                    $sql_image = "INSERT INTO images (listing_id, filename) VALUES (?, ?)";
                    $stmt_image = $conn->prepare($sql_image);
                    if ($stmt_image) {
                        $stmt_image->bind_param("is", $boarding_house_id, $filename);
                        $stmt_image->execute();
                        $stmt_image->close();
                    }
                } catch (Exception $e) {
                    // Skip if table doesn't exist or column is different
                    if (DEBUG) {
                        error_log("Images table insert skipped: " . $e->getMessage());
                    }
                }
                
                // Insert into bh_images table
                try {
                    $sql_bh_image = "INSERT INTO bh_images (bh_id, image_path, is_primary) VALUES (?, ?, ?)";
                    $stmt_bh_image = $conn->prepare($sql_bh_image);
                    if ($stmt_bh_image) {
                        $stmt_bh_image->bind_param("isi", $boarding_house_id, $image_path, $is_primary);
                        $stmt_bh_image->execute();
                        $stmt_bh_image->close();
                    }
                } catch (Exception $e) {
                    if (DEBUG) {
                        error_log("BH Images table insert skipped: " . $e->getMessage());
                    }
                }
                
                // Insert into photos table
                try {
                    $sql_photo = "INSERT INTO photos (boarding_house_id, photo_url, is_primary) VALUES (?, ?, ?)";
                    $stmt_photo = $conn->prepare($sql_photo);
                    if ($stmt_photo) {
                        $stmt_photo->bind_param("isi", $boarding_house_id, $image_path, $is_primary);
                        $stmt_photo->execute();
                        $stmt_photo->close();
                    }
                } catch (Exception $e) {
                    if (DEBUG) {
                        error_log("Photos table insert skipped: " . $e->getMessage());
                    }
                }
                
                // Update boarding_houses.image with first image
                if ($is_first_image) {
                    $sql_update_main = "UPDATE boarding_houses SET image = ? WHERE id = ?";
                    $stmt_update = $conn->prepare($sql_update_main);
                    $stmt_update->bind_param("si", $image_path, $boarding_house_id);
                    $stmt_update->execute();
                    $stmt_update->close();
                    
                    $is_first_image = false;
                }
                
                $uploaded_count++;
            }
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Log activity
    $action = "Added new listing";
    $description = "Created boarding house listing: $title";
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $sql_log = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)";
    $stmt_log = $conn->prepare($sql_log);
    if ($stmt_log) {
        $stmt_log->bind_param("issss", $user_id, $action, $description, $ip_address, $user_agent);
        $stmt_log->execute();
        $stmt_log->close();
    }
    
    // Set success message
    $_SESSION['success'] = "Listing added successfully!";
    header('Location: /board-in/bh_manager/my-listings.php');
    exit;
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log error if debug mode
    if (DEBUG) {
        error_log("Error adding listing: " . $e->getMessage());
        $_SESSION['error'] = "Error adding listing: " . $e->getMessage();
    } else {
        $_SESSION['error'] = "An error occurred while adding the listing. Please try again.";
    }
    
    header('Location: /board-in/bh_manager/add-listing.php');
    exit;
}
?>