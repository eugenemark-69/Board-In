-- Migration: add feature boolean columns to boarding_houses
ALTER TABLE boarding_houses
  ADD COLUMN wifi TINYINT(1) DEFAULT 0,
  ADD COLUMN laundry TINYINT(1) DEFAULT 0,
  ADD COLUMN kitchen TINYINT(1) DEFAULT 0,
  ADD COLUMN close_to_bipsu TINYINT(1) DEFAULT 0;

-- After running this migration, managers can set features when creating listings.
