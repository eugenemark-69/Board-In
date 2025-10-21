-- Migration: add city column and insert 10 sample boarding_houses with expanded schema
-- Adds a `city` column (nullable) and inserts sample rows with new fields and amenities.

ALTER TABLE boarding_houses
  ADD COLUMN city VARCHAR(100) DEFAULT NULL;

-- Insert 10 sample boarding houses (manager_id=2 used as sample landlord)
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

-- Create matching amenities rows for each inserted boarding house
INSERT INTO amenities (boarding_house_id, wifi, own_cr, shared_kitchen, laundry_area, parking, study_area, air_conditioning, water_heater)
VALUES
(LAST_INSERT_ID() - 9, 1, 1, 0, 0, 0, 1, 0, 1),
(LAST_INSERT_ID() - 8, 1, 0, 1, 1, 0, 0, 0, 1),
(LAST_INSERT_ID() - 7, 1, 1, 1, 0, 0, 0, 1, 1),
(LAST_INSERT_ID() - 6, 1, 1, 0, 0, 0, 0, 1, 1),
(LAST_INSERT_ID() - 5, 0, 0, 1, 1, 1, 0, 1, 1),
(LAST_INSERT_ID() - 4, 1, 1, 0, 0, 0, 1, 0, 1),
(LAST_INSERT_ID() - 3, 1, 1, 0, 0, 0, 0, 1, 1),
(LAST_INSERT_ID() - 2, 1, 1, 1, 0, 0, 0, 0, 1),
(LAST_INSERT_ID() - 1, 1, 0, 1, 1, 0, 1, 0, 1),
(LAST_INSERT_ID(), 1, 1, 0, 0, 1, 1, 1, 1);

-- Note: The LAST_INSERT_ID() arithmetic above assumes no other inserts into boarding_houses
-- between the INSERT and the following INSERTs; if applying to an existing DB, replace with explicit IDs.
