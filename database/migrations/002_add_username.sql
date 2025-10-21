-- Migration: add username column to users
ALTER TABLE users
  ADD COLUMN username VARCHAR(100) UNIQUE AFTER email;

-- After running this migration, registration will require a username.
