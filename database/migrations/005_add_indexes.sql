-- Migration: add indexes to speed up search queries

-- boarding_houses indexes
ALTER TABLE boarding_houses
  ADD INDEX idx_monthly_rent (monthly_rent),
  ADD INDEX idx_available_rooms (available_rooms),
  ADD INDEX idx_lat_lng (latitude, longitude),
  ADD INDEX idx_gender_allowed (gender_allowed),
  ADD INDEX idx_status (status);

-- amenities index
CREATE INDEX idx_amenities_bhid ON amenities (boarding_house_id);

-- reviews index
CREATE INDEX idx_reviews_listing ON reviews (listing_id);

-- photos index
CREATE INDEX idx_photos_bhid ON photos (boarding_house_id);

-- transactions index
CREATE INDEX idx_transactions_booking ON transactions (booking_id);

-- End of migration 005
