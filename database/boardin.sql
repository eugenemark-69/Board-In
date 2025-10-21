-- Starter schema for Board-In
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

-- Optional improved photos table for new features
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

-- Transactions table for payments
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

INSERT INTO users (email, password, role) VALUES
('student1@example.com', '$2y$10$wH1K1mFjQG6Y2k1pYyq9EOh0q.u4gq9R1e1o8cQfO8sQf9JZl9V4G', 'student'),
('manager1@example.com', '$2y$10$wH1K1mFjQG6Y2k1pYyq9EOh0q.u4gq9R1e1o8cQfO8sQf9JZl9V4G', 'landlord'),
('admin@example.com', '$2y$10$wH1K1mFjQG6Y2k1pYyq9EOh0q.u4gq9R1e1o8cQfO8sQf9JZl9V4G', 'admin');

INSERT INTO boarding_houses (manager_id, title, description) VALUES
(2, 'Cozy Dorm Near Campus', 'A clean, comfortable single room near campus with fast WiFi and private bathroom.'),
(2, 'Affordable Shared House', 'Shared accommodation with friendly roommates, included utilities.'),
(2, 'Studio Apartment', 'Studio with kitchenette, 10 minutes walk to campus.');

-- images can be added into uploads/ after importing
