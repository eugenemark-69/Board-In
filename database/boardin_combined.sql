-- Combined Board-In SQL
-- Order: base schema -> feature migrations -> username migration -> full features migration -> sample listings -> indexes
-- Run this file on a fresh database. If applying to an existing DB, review for duplicate column/table errors.

-- ==========================
-- Base schema (boardin.sql)
-- ==========================
CREATE DATABASE IF NOT EXISTS boardin DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE boardin;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  -- unified role/user_type and extra profile columns
  user_type ENUM('student','landlord','admin') DEFAULT 'student',
  full_name VARCHAR(255) DEFAULT NULL,
  contact_number VARCHAR(50) DEFAULT NULL,
  email_verified TINYINT(1) DEFAULT 0,
  verification_token VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE boarding_houses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  manager_id INT NOT NULL,
  landlord_id INT DEFAULT NULL,
  title VARCHAR(255) NOT NULL,
  name VARCHAR(255) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  latitude DECIMAL(10,7) DEFAULT NULL,
  longitude DECIMAL(10,7) DEFAULT NULL,
  monthly_rent DECIMAL(10,2) DEFAULT 0.00,
  security_deposit DECIMAL(10,2) DEFAULT 0.00,
  available_rooms INT DEFAULT 0,
  total_rooms INT DEFAULT 0,
  gender_allowed ENUM('male','female','both') DEFAULT 'both',
  description TEXT,
  house_rules TEXT DEFAULT NULL,
  curfew_time TIME DEFAULT NULL,
  status ENUM('available','full','inactive') DEFAULT 'available',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  listing_id INT NOT NULL,
  filename VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (listing_id) REFERENCES boarding_houses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS photos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  boarding_house_id INT NOT NULL,
  photo_url VARCHAR(500) NOT NULL,
  is_primary TINYINT(1) DEFAULT 0,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE
);

CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  listing_id INT NOT NULL,
  student_id INT NOT NULL,
  landlord_id INT DEFAULT NULL,
  booking_reference VARCHAR(100) UNIQUE DEFAULT NULL,
  move_in_date DATE DEFAULT NULL,
  monthly_rent DECIMAL(10,2) DEFAULT 0.00,
  security_deposit DECIMAL(10,2) DEFAULT 0.00,
  total_amount DECIMAL(10,2) DEFAULT 0.00,
  commission_amount DECIMAL(10,2) DEFAULT 0.00,
  payment_status ENUM('pending','paid','refunded') DEFAULT 'pending',
  payment_reference VARCHAR(255) DEFAULT NULL,
  booking_status ENUM('pending','confirmed','active','completed','cancelled') DEFAULT 'pending',
  landlord_confirmed_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (listing_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  listing_id INT NOT NULL,
  booking_id INT DEFAULT NULL,
  student_id INT NOT NULL,
  rating TINYINT NOT NULL,
  cleanliness_rating TINYINT DEFAULT NULL,
  location_rating TINYINT DEFAULT NULL,
  value_rating TINYINT DEFAULT NULL,
  landlord_rating TINYINT DEFAULT NULL,
  comment TEXT,
  landlord_response TEXT DEFAULT NULL,
  responded_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (listing_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS transactions (
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
);

-- ==========================
-- Migration 001: Add feature boolean columns
-- ==========================
ALTER TABLE boarding_houses
  ADD COLUMN wifi TINYINT(1) DEFAULT 0,
  ADD COLUMN laundry TINYINT(1) DEFAULT 0,
  ADD COLUMN kitchen TINYINT(1) DEFAULT 0,
  ADD COLUMN close_to_bipsu TINYINT(1) DEFAULT 0;

-- ==========================
-- Migration 002: Add username column
-- ==========================
ALTER TABLE users
  ADD COLUMN username VARCHAR(100) UNIQUE AFTER email;

-- ==========================
-- Migration 004: Full features (landlords, amenities, photos, transactions, enhancements)
-- ==========================
ALTER TABLE users
  ADD COLUMN full_name VARCHAR(255) DEFAULT NULL,
  ADD COLUMN contact_number VARCHAR(50) DEFAULT NULL,
  ADD COLUMN user_type ENUM('student','landlord','admin') DEFAULT 'student',
  ADD COLUMN email_verified TINYINT(1) DEFAULT 0,
  ADD COLUMN verification_token VARCHAR(255) DEFAULT NULL,
  ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL;

CREATE TABLE IF NOT EXISTS landlords (
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
);

ALTER TABLE boarding_houses
  ADD COLUMN name VARCHAR(255) NULL AFTER title,
  ADD COLUMN address TEXT DEFAULT NULL,
  ADD COLUMN latitude DECIMAL(10,7) DEFAULT NULL,
  ADD COLUMN longitude DECIMAL(10,7) DEFAULT NULL,
  ADD COLUMN monthly_rent DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN security_deposit DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN available_rooms INT DEFAULT 0,
  ADD COLUMN total_rooms INT DEFAULT 0,
  ADD COLUMN gender_allowed ENUM('male','female','both') DEFAULT 'both',
  ADD COLUMN house_rules TEXT DEFAULT NULL,
  ADD COLUMN curfew_time TIME DEFAULT NULL,
  ADD COLUMN status ENUM('available','full','inactive') DEFAULT 'available',
  ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE boarding_houses
  ADD COLUMN landlord_id INT DEFAULT NULL;

ALTER TABLE boarding_houses
  ADD CONSTRAINT fk_boarding_houses_landlord FOREIGN KEY (landlord_id) REFERENCES landlords(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS amenities (
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
);

CREATE TABLE IF NOT EXISTS photos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  boarding_house_id INT NOT NULL,
  photo_url VARCHAR(500) NOT NULL,
  is_primary TINYINT(1) DEFAULT 0,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE
);

ALTER TABLE bookings
  ADD COLUMN booking_reference VARCHAR(100) UNIQUE DEFAULT NULL,
  ADD COLUMN landlord_id INT DEFAULT NULL,
  ADD COLUMN move_in_date DATE DEFAULT NULL,
  ADD COLUMN monthly_rent DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN security_deposit DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN total_amount DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN commission_amount DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN payment_status ENUM('pending','paid','refunded') DEFAULT 'pending',
  ADD COLUMN payment_reference VARCHAR(255) DEFAULT NULL,
  ADD COLUMN booking_status ENUM('pending','confirmed','active','completed','cancelled') DEFAULT 'pending',
  ADD COLUMN landlord_confirmed_at TIMESTAMP NULL DEFAULT NULL,
  ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE bookings
  ADD CONSTRAINT fk_bookings_landlord FOREIGN KEY (landlord_id) REFERENCES landlords(id) ON DELETE SET NULL;

ALTER TABLE reviews
  ADD COLUMN booking_id INT DEFAULT NULL,
  ADD COLUMN cleanliness_rating TINYINT DEFAULT NULL,
  ADD COLUMN location_rating TINYINT DEFAULT NULL,
  ADD COLUMN value_rating TINYINT DEFAULT NULL,
  ADD COLUMN landlord_rating TINYINT DEFAULT NULL,
  ADD COLUMN landlord_response TEXT DEFAULT NULL,
  ADD COLUMN responded_at TIMESTAMP NULL DEFAULT NULL,
  ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE reviews
  ADD CONSTRAINT fk_reviews_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS transactions (
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
);

-- ==========================
-- Migration 003: Sample listings (10 rows) and amenities
-- ==========================
ALTER TABLE boarding_houses
  ADD COLUMN city VARCHAR(100) DEFAULT NULL;

INSERT INTO boarding_houses (manager_id, landlord_id, title, name, address, latitude, longitude, monthly_rent, security_deposit, available_rooms, total_rooms, gender_allowed, description, house_rules, curfew_time, status, city)
VALUES
(2, 2, 'Naval Cozy Single Room', 'Cozy Single', 'Brgy. Naval Rd, Naval', 10.2065, 125.4841, 2500.00, 1000.00, 3, 3, 'both', 'Comfortable single room a short walk from campus. Includes WiFi and utilities.', 'No smoking inside; keep common areas clean', '23:00:00', 'available', 'Naval'),
(2, 2, 'Naval Shared House - Budget', 'Shared House', 'Lt. Gomez St, Naval', 10.2058, 125.4835, 1500.00, 500.00, 5, 5, 'both', 'Shared bedroom in a friendly house. Great for students on a budget.', 'Shoes off inside; rotate cleaning duties', NULL, 'available', 'Naval'),
(2, 2, 'Naval Studio with Kitchenette', 'Studio Kitchenette', 'San Miguel Ave, Naval', 10.2072, 125.4860, 4200.00, 1500.00, 1, 1, 'both', 'Compact studio perfect for one person, with small kitchenette and storage.', 'No pets', NULL, 'available', 'Naval'),
(2, 2, 'Almeria Modern Studio', 'Modern Studio', 'Almeria St, Almeria', 10.2021, 125.4875, 4800.00, 1500.00, 1, 1, 'both', 'Bright studio apartment with modern amenities and close to transport.', 'No loud parties after 10PM', NULL, 'available', 'Almeria'),
(2, 2, 'Almeria Spacious 2BR', '2BR Spacious', 'Market Road, Almeria', 10.2015, 125.4890, 6500.00, 2000.00, 2, 2, 'both', 'Two-bedroom unit suitable for roommates, near markets and BIPSU.', 'Shared utilities, respect quiet hours', NULL, 'available', 'Almeria'),
(2, 2, 'Almeria Quiet Room', 'Quiet Room', 'Peaceful Lane, Almeria', 10.2030, 125.4868, 2200.00, 800.00, 1, 1, 'both', 'Private room in a quiet neighborhood, laundry available nearby.', 'No parties', NULL, 'available', 'Almeria'),
(2, 2, 'Naval Newly Renovated Room', 'Renovated Room', 'Rehabilitation Rd, Naval', 10.2080, 125.4825, 3000.00, 1000.00, 2, 2, 'both', 'Recently renovated, high-speed WiFi included, walking distance to campus.', 'Keep appliances clean', NULL, 'available', 'Naval'),
(2, 2, 'Almeria Furnished Studio', 'Furnished Studio', 'Sunset Blvd, Almeria', 10.2008, 125.4882, 5200.00, 1500.00, 1, 1, 'both', 'Fully furnished studio with bed, desk, and kitchen essentials.', 'No smoking', NULL, 'available', 'Almeria'),
(2, 2, 'Naval Family House Shared', 'Family Shared', 'Oak St, Naval', 10.2044, 125.4819, 1800.00, 500.00, 4, 4, 'both', 'Shared house with backyard, utilities included. Friendly environment.', 'Respect family hours', NULL, 'available', 'Naval'),
(2, 2, 'Almeria Penthouse Studio', 'Penthouse Studio', 'Hilltop Ave, Almeria', 10.1999, 125.4901, 7200.00, 2500.00, 1, 1, 'both', 'Penthouse-level studio with great views and fast internet.', 'No pets; tenant must maintain balcony plants', NULL, 'available', 'Almeria');

-- Capture the first inserted id and create amenities rows using offsets (safer than arithmetic on LAST_INSERT_ID() )
SET @first = LAST_INSERT_ID();

INSERT INTO amenities (boarding_house_id, wifi, own_cr, shared_kitchen, laundry_area, parking, study_area, air_conditioning, water_heater)
VALUES
(@first + 0, 1, 1, 0, 0, 0, 1, 0, 1),
(@first + 1, 1, 0, 1, 1, 0, 0, 0, 1),
(@first + 2, 1, 1, 1, 0, 0, 0, 1, 1),
(@first + 3, 1, 1, 0, 0, 0, 0, 1, 1),
(@first + 4, 0, 0, 1, 1, 1, 0, 1, 1),
(@first + 5, 1, 1, 0, 0, 0, 1, 0, 1),
(@first + 6, 1, 1, 0, 0, 0, 0, 1, 1),
(@first + 7, 1, 1, 1, 0, 0, 0, 0, 1),
(@first + 8, 1, 0, 1, 1, 0, 1, 0, 1),
(@first + 9, 1, 1, 0, 0, 1, 1, 1, 1);

-- ==========================
-- Migration 005: Indexes
-- ==========================

ALTER TABLE boarding_houses
  ADD INDEX idx_monthly_rent (monthly_rent),
  ADD INDEX idx_available_rooms (available_rooms),
  ADD INDEX idx_lat_lng (latitude, longitude),
  ADD INDEX idx_gender_allowed (gender_allowed),
  ADD INDEX idx_status (status);

CREATE INDEX idx_amenities_bhid ON amenities (boarding_house_id);

CREATE INDEX idx_reviews_listing ON reviews (listing_id);

CREATE INDEX idx_photos_bhid ON photos (boarding_house_id);

CREATE INDEX idx_transactions_booking ON transactions (booking_id);

-- End of combined SQL
