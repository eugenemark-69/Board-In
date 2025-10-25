-- Run this SQL to add any missing columns

-- Add payment columns to bookings if not exist
ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS payment_status ENUM('unpaid', 'partial', 'paid', 'pending', 'refunded') DEFAULT 'unpaid',
ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS commission_amount DECIMAL(10,2) DEFAULT 0.00;

-- Ensure transactions table has all required fields
ALTER TABLE transactions 
ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT 'GCash',
ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS processed_at TIMESTAMP NULL DEFAULT NULL;

-- Add indexes for performance
CREATE INDEX IF NOT EXISTS idx_payment_status ON bookings(payment_status);
CREATE INDEX IF NOT EXISTS idx_payment_reference ON bookings(payment_reference);
CREATE INDEX IF NOT EXISTS idx_transaction_status ON transactions(status);