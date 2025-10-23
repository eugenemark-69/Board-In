-- Add columns to boarding_houses table
ALTER TABLE boarding_houses 
ADD COLUMN verification_status ENUM('unverified','pending_verification','verified','rejected') DEFAULT 'unverified' AFTER status,
ADD COLUMN verification_requested_at TIMESTAMP NULL DEFAULT NULL AFTER verification_status,
ADD COLUMN verification_completed_at TIMESTAMP NULL DEFAULT NULL AFTER verification_requested_at,
ADD COLUMN verification_rejection_count INT DEFAULT 0 AFTER verification_completed_at,
ADD COLUMN verification_notes TEXT DEFAULT NULL AFTER verification_rejection_count,
ADD COLUMN verified_by_admin_id INT DEFAULT NULL AFTER verification_notes,
ADD COLUMN documents_submitted_at TIMESTAMP NULL DEFAULT NULL AFTER verified_by_admin_id,
ADD COLUMN last_verified_at TIMESTAMP NULL DEFAULT NULL AFTER documents_submitted_at;

-- Create verification documents table
CREATE TABLE IF NOT EXISTS bh_verification_docs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bh_id INT NOT NULL,
    doc_type ENUM('valid_id','business_permit','proof_ownership','barangay_clearance') NOT NULL,
    file_url VARCHAR(500) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bh_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
    INDEX idx_bh_id (bh_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create verification visits table
CREATE TABLE IF NOT EXISTS bh_verification_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bh_id INT NOT NULL,
    scheduled_date DATE DEFAULT NULL,
    completed_date DATE DEFAULT NULL,
    verified_by VARCHAR(100) DEFAULT NULL,
    visit_notes TEXT DEFAULT NULL,
    photos_match BOOLEAN DEFAULT NULL,
    amenities_match BOOLEAN DEFAULT NULL,
    address_confirmed BOOLEAN DEFAULT NULL,
    status ENUM('scheduled','completed','cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bh_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
    INDEX idx_bh_id (bh_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;