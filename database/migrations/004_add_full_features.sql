-- Migration: add full feature tables and columns from appExpansion.md
-- 1) Enhance users table
ALTER TABLE users
  ADD COLUMN full_name VARCHAR(255) DEFAULT NULL,
  ADD COLUMN contact_number VARCHAR(50) DEFAULT NULL,
  ADD COLUMN user_type ENUM('student','landlord','admin') DEFAULT 'student',
  ADD COLUMN email_verified TINYINT(1) DEFAULT 0,
  ADD COLUMN verification_token VARCHAR(255) DEFAULT NULL,
  ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL;

-- 2) Create landlords table (holds verification documents and payment info)
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

-- 3) Enhance boarding_houses table
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

-- If your app currently links boarding_houses to users via manager_id, also create landlord linkage convenience column
ALTER TABLE boarding_houses
  ADD COLUMN landlord_id INT DEFAULT NULL;

-- Add foreign key to landlords if landlords table is used; if not, keep manager_id as-is.
ALTER TABLE boarding_houses
  ADD CONSTRAINT fk_boarding_houses_landlord FOREIGN KEY (landlord_id) REFERENCES landlords(id) ON DELETE SET NULL;

-- 4) Amenities table
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

-- 5) Photos table (new, more featureful than legacy images table)
CREATE TABLE IF NOT EXISTS photos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  boarding_house_id INT NOT NULL,
  photo_url VARCHAR(500) NOT NULL,
  is_primary TINYINT(1) DEFAULT 0,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE
);

-- 6) Enhance bookings table with payment/commission fields
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

-- 7) Enhance reviews table to associate with bookings and include category ratings
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

-- 8) Transactions table
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

-- End of migration 004
