<?php
/**
 * Board-In - Database Configuration File
 * This file contains all database connection settings and auto-installation
 */

// Database configuration - update with your local credentials
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'boardin');
define('DB_USER', 'root');
define('DB_PASS', '');

// Debug mode - set to true on development machines to show full errors (DO NOT enable in production)
if (!defined('DEBUG')) define('DEBUG', true);

// Payment / platform settings (set real values in a local config or env)
define('PLATFORM_GCASH_NUMBER', '');
define('PAYMENT_PROVIDER_SECRET', ''); // e.g. PayMongo webhook secret or shared token
define('PLATFORM_COMMISSION_RATE', 0.03); // 3% commission by default

// Upload directories
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/board-in/uploads/');
define('PROFILE_UPLOAD_DIR', UPLOAD_DIR . 'profiles/');
define('PROFILE_UPLOAD_URL', '/board-in/uploads/profiles/');

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}
if (!file_exists(PROFILE_UPLOAD_DIR)) {
    mkdir(PROFILE_UPLOAD_DIR, 0777, true);
}

// ============================================
// AUTO-INSTALLATION FEATURE
// This checks if database exists, if not, creates it automatically
// ============================================

function auto_install_database() {
    // Connect to MySQL server without selecting database
    $conn_check = @new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn_check->connect_error) {
        die("<h1>Database Connection Error</h1><p>Cannot connect to MySQL. Please check XAMPP is running.</p><p>Error: " . $conn_check->connect_error . "</p>");
    }
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if (!$conn_check->query($sql)) {
        die("<h1>Database Creation Error</h1><p>Could not create database: " . $conn_check->error . "</p>");
    }
    
    // Now select the database
    if (!$conn_check->select_db(DB_NAME)) {
        die("<h1>Database Selection Error</h1><p>Could not select database: " . $conn_check->error . "</p>");
    }
    
    // Check if tables exist by checking information_schema
    $table_check = $conn_check->query("SHOW TABLES LIKE 'users'");
    
    if ($table_check->num_rows == 0) {
        // Tables don't exist, create them
        create_all_tables($conn_check);
        insert_sample_data($conn_check);
    } else {
        // Tables exist, check if profile columns exist
        update_users_table_for_profiles($conn_check);
    }
    
    $conn_check->close();
}

function update_users_table_for_profiles($conn) {
    // Add new profile-related columns if they don't exist
    $columns_to_add = [
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS bio TEXT DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS address VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS date_of_birth DATE DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS gender ENUM('male', 'female', 'other', 'prefer_not_to_say') DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS student_id VARCHAR(50) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS school VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS facebook_url VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS twitter_url VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS linkedin_url VARCHAR(255) DEFAULT NULL"
    ];
    
    foreach ($columns_to_add as $query) {
        // Try to add column, ignore if it already exists
        @$conn->query($query);
    }
}

function create_all_tables($conn) {
    $queries = [
        // USERS TABLE - Enhanced with profile fields
        "CREATE TABLE IF NOT EXISTS users (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            username VARCHAR(100) UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(255) DEFAULT NULL,
            phone VARCHAR(20),
            contact_number VARCHAR(50) DEFAULT NULL,
            role ENUM('tenant', 'landlord', 'admin') DEFAULT 'tenant',
            user_type ENUM('student','landlord','admin') DEFAULT 'student',
            profile_picture VARCHAR(255),
            bio TEXT DEFAULT NULL,
            address VARCHAR(255) DEFAULT NULL,
            date_of_birth DATE DEFAULT NULL,
            gender ENUM('male', 'female', 'other', 'prefer_not_to_say') DEFAULT NULL,
            student_id VARCHAR(50) DEFAULT NULL,
            school VARCHAR(255) DEFAULT NULL,
            facebook_url VARCHAR(255) DEFAULT NULL,
            twitter_url VARCHAR(255) DEFAULT NULL,
            linkedin_url VARCHAR(255) DEFAULT NULL,
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
            email_verified TINYINT(1) DEFAULT 0,
            verification_token VARCHAR(255) DEFAULT NULL,
            last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_username (username),
            INDEX idx_role (role)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // LANDLORDS TABLE
        "CREATE TABLE IF NOT EXISTS landlords (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            business_name VARCHAR(255) DEFAULT NULL,
            valid_id_url VARCHAR(500) DEFAULT NULL,
            proof_of_ownership_url VARCHAR(500) DEFAULT NULL,
            barangay_clearance_url VARCHAR(500) DEFAULT NULL,
            verification_status ENUM('pending','approved','rejected') DEFAULT 'pending',
            verified_at TIMESTAMP NULL DEFAULT NULL,
            gcash_number VARCHAR(50) DEFAULT NULL,
            commission_owed DECIMAL(10,2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // BOARDING HOUSES TABLE
        "CREATE TABLE IF NOT EXISTS boarding_houses (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            manager_id INT(11) DEFAULT NULL,
            landlord_id INT DEFAULT NULL,
            name VARCHAR(200) NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            address VARCHAR(255) NOT NULL,
            location VARCHAR(100) NOT NULL,
            city VARCHAR(100) NOT NULL,
            province VARCHAR(50) NOT NULL,
            latitude DECIMAL(10, 7),
            longitude DECIMAL(11, 7),
            price DECIMAL(10,2) NOT NULL,
            monthly_rent DECIMAL(10,2) DEFAULT 0.00,
            security_deposit DECIMAL(10,2) DEFAULT 0.00,
            room_type ENUM('single', 'shared', 'studio') NOT NULL,
            available_rooms INT(11) DEFAULT 1,
            total_rooms INT(11) DEFAULT 1,
            gender_allowed ENUM('male','female','both') DEFAULT 'both',
            image VARCHAR(255),
            wifi BOOLEAN DEFAULT FALSE,
            parking BOOLEAN DEFAULT FALSE,
            kitchen BOOLEAN DEFAULT FALSE,
            laundry BOOLEAN DEFAULT FALSE,
            aircon BOOLEAN DEFAULT FALSE,
            water_heater BOOLEAN DEFAULT FALSE,
            security BOOLEAN DEFAULT FALSE,
            cctv BOOLEAN DEFAULT FALSE,
            close_to_bipsu TINYINT(1) DEFAULT 0,
            house_rules TEXT DEFAULT NULL,
            curfew_time TIME DEFAULT NULL,
            contact_phone VARCHAR(20),
            contact_name VARCHAR(100),
            contact_email VARCHAR(100),
            status ENUM('active', 'inactive', 'pending', 'rejected', 'available', 'full') DEFAULT 'pending',
            views INT(11) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_location (location),
            INDEX idx_city (city),
            INDEX idx_price (price),
            INDEX idx_room_type (room_type),
            INDEX idx_status (status),
            INDEX idx_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // AMENITIES TABLE
        "CREATE TABLE IF NOT EXISTS amenities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            boarding_house_id INT NOT NULL,
            wifi TINYINT(1) DEFAULT 0,
            own_cr TINYINT(1) DEFAULT 0,
            shared_kitchen TINYINT(1) DEFAULT 0,
            laundry_area TINYINT(1) DEFAULT 0,
            parking TINYINT(1) DEFAULT 0,
            study_area TINYINT(1) DEFAULT 0,
            air_conditioning TINYINT(1) DEFAULT 0,
            water_heater TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // IMAGES TABLE
        "CREATE TABLE IF NOT EXISTS images (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            listing_id INT(11) NOT NULL,
            filename VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (listing_id) REFERENCES boarding_houses(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // BH IMAGES TABLE
        "CREATE TABLE IF NOT EXISTS bh_images (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            bh_id INT(11) NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            is_primary BOOLEAN DEFAULT FALSE,
            upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (bh_id) REFERENCES boarding_houses(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // PHOTOS TABLE
        "CREATE TABLE IF NOT EXISTS photos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            boarding_house_id INT NOT NULL,
            photo_url VARCHAR(500) NOT NULL,
            is_primary TINYINT(1) DEFAULT 0,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // FAVORITES TABLE
        "CREATE TABLE IF NOT EXISTS favorites (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            bh_id INT(11) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (bh_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
            UNIQUE KEY unique_favorite (user_id, bh_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // BOOKINGS TABLE
        "CREATE TABLE IF NOT EXISTS bookings (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            bh_id INT(11) NOT NULL,
            listing_id INT(11) DEFAULT NULL,
            user_id INT(11) NOT NULL,
            student_id INT(11) DEFAULT NULL,
            landlord_id INT DEFAULT NULL,
            booking_reference VARCHAR(100) UNIQUE DEFAULT NULL,
            check_in_date DATE NOT NULL,
            move_in_date DATE DEFAULT NULL,
            duration_months INT(11) DEFAULT 1,
            monthly_rent DECIMAL(10,2) DEFAULT 0.00,
            security_deposit DECIMAL(10,2) DEFAULT 0.00,
            total_amount DECIMAL(10,2) NOT NULL,
            commission_amount DECIMAL(10,2) DEFAULT 0.00,
            status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
            payment_status ENUM('unpaid', 'partial', 'paid', 'pending', 'refunded') DEFAULT 'unpaid',
            payment_reference VARCHAR(255) DEFAULT NULL,
            booking_status ENUM('pending','confirmed','active','completed','cancelled') DEFAULT 'pending',
            landlord_confirmed_at TIMESTAMP NULL DEFAULT NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (bh_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
            FOREIGN KEY (listing_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // REVIEWS TABLE
        "CREATE TABLE IF NOT EXISTS reviews (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            bh_id INT(11) NOT NULL,
            listing_id INT(11) DEFAULT NULL,
            user_id INT(11) NOT NULL,
            student_id INT(11) DEFAULT NULL,
            booking_id INT DEFAULT NULL,
            rating INT(1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
            cleanliness_rating TINYINT DEFAULT NULL,
            location_rating TINYINT DEFAULT NULL,
            value_rating TINYINT DEFAULT NULL,
            landlord_rating TINYINT DEFAULT NULL,
            comment TEXT,
            landlord_response TEXT DEFAULT NULL,
            responded_at TIMESTAMP NULL DEFAULT NULL,
            status ENUM('approved', 'pending', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (bh_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
            FOREIGN KEY (listing_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // INQUIRIES TABLE
        "CREATE TABLE IF NOT EXISTS inquiries (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            bh_id INT(11) NOT NULL,
            sender_id INT(11) NOT NULL,
            receiver_id INT(11) NOT NULL,
            subject VARCHAR(200),
            message TEXT NOT NULL,
            status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (bh_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // TRANSACTIONS TABLE
        "CREATE TABLE IF NOT EXISTS transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            booking_id INT DEFAULT NULL,
            transaction_type ENUM('booking_payment','commission_payment') NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_method VARCHAR(50) DEFAULT 'GCash',
            payment_reference VARCHAR(255) DEFAULT NULL,
            status ENUM('pending','completed','failed') DEFAULT 'pending',
            processed_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // NOTIFICATIONS TABLE
        "CREATE TABLE IF NOT EXISTS notifications (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            title VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
            is_read BOOLEAN DEFAULT FALSE,
            link VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // ACTIVITY LOGS TABLE
        "CREATE TABLE IF NOT EXISTS activity_logs (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11),
            action VARCHAR(100) NOT NULL,
            description TEXT,
            ip_address VARCHAR(45),
            user_agent VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
    
    foreach ($queries as $query) {
        if (!$conn->query($query)) {
            if (DEBUG) {
                error_log("Table creation error: " . $conn->error);
            }
        }
    }
}

function insert_sample_data($conn) {
    $sample_users = [
        "INSERT INTO users (username, email, password, full_name, role, user_type, status) VALUES
        ('admin', 'admin@boardin.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', 'admin', 'active')",
        
        "INSERT INTO users (username, email, password, full_name, phone, role, user_type, status) VALUES
        ('landlord1', 'landlord@example.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', '09123456789', 'landlord', 'landlord', 'active')",
        
        "INSERT INTO users (username, email, password, full_name, phone, role, user_type, status) VALUES
        ('tenant1', 'tenant@example.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', '09987654321', 'tenant', 'student', 'active')"
    ];
    
    foreach ($sample_users as $query) {
        @$conn->query($query);
    }
}

// Run auto-installation check
auto_install_database();

// Create MySQLi connection for normal use
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Set charset
$conn->set_charset('utf8mb4');

// helper functions are provided from includes/functions.php

?>