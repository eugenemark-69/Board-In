 this are web app exapnsions more feature to be implemented
 
 
 User Roles & Permissions
1. Student
Can:

Register with @bipsu.edu.ph email
Search and filter boarding houses
View boarding house details and photos
Book boarding houses (with payment)
Write reviews (only for places they've stayed)
View their booking history
Save favorite listings

Cannot:

Post listings
Access admin features
Review without verified booking

2. Landlord
Can:

Register and submit verification documents
Post boarding house listings (after verification)
Upload multiple photos
Edit/update their listings
View inquiries and bookings
Confirm student move-ins
Respond to reviews
View their earnings dashboard
Mark rooms as available/occupied

Cannot:

Book other boarding houses (unless separate student account)
Access other landlords' data
Approve their own verification

3. Admin (You)
Can:

Approve/reject landlord verifications
Monitor all transactions
View commission reports
Handle disputes
Deactivate listings or users
View all analytics
Send announcements
Moderate reviews


🎨 Key Pages & Features
Public Pages (No Login Required)
1. Homepage

Hero section with search bar (location, price range)
Featured boarding houses
How it works (3 steps)
Stats (e.g., "50+ verified boarding houses")
Call-to-action buttons (For Students / For Landlords)

2. Search Results Page
Filters:

Price range (₱1,500 - ₱10,000)
Gender allowed
Distance from BIPSU (walkable, <1km, <2km, <5km)
Amenities (WiFi, own CR, kitchen, etc.)
Available rooms
With/without curfew

Display:

Grid or list view toggle
Map view option
Sort by: Price (low/high), Rating, Newest
Each card shows: Photo, name, price, rating, distance, key amenities

3. Boarding House Details Page

Photo gallery (carousel)
Price and availability
Address with map
Full amenities list
House rules
Description
Reviews section (ratings breakdown + comments)
Landlord info (name, contact, response rate)
"Book Now" button

4. About/How It Works Page

For students: How to find and book
For landlords: How to list and manage
Safety and verification process
FAQ section


Student Pages (Login Required)
5. Student Dashboard

Active bookings
Booking history
Saved/favorited listings
Pending reviews
Profile settings

6. Booking Page

Summary of selected boarding house
Move-in date selector
Payment breakdown:

Monthly rent: ₱X,XXX
Security deposit: ₱X,XXX
Total: ₱X,XXX


GCash payment button
Terms and conditions checkbox

7. Booking Confirmation Page

Booking reference number
Payment receipt
Landlord contact info
Next steps (wait for landlord confirmation)
Download receipt button

8. Write Review Page

Only accessible after confirmed stay
Overall rating (stars)
Category ratings (cleanliness, location, value, landlord)
Written review (text area)
Photo upload (optional)


Landlord Pages (Login Required)
9. Landlord Dashboard

Overview stats (total bookings, active tenants, earnings)
Pending bookings (to confirm)
Active bookings
Commission owed
Quick actions (Add new listing, Edit listing)

10. Add/Edit Listing Page
Form Fields:

Basic Info: Name, address, contact number
Pricing: Monthly rent, security deposit
Capacity: Total rooms, available rooms
Gender restrictions
Amenities (checkboxes)
House rules (textarea)
Curfew time (if applicable)
Description
Photos (upload up to 10)
Map location picker

11. Manage Bookings Page

List of all bookings
Filter by status (pending, active, completed)
Action buttons:

Confirm booking (after student moves in)
Contact student
View details



12. Reviews Management Page

All reviews for their properties
Ability to respond to reviews
Average ratings display

13. Verification Page (First-time Setup)
Upload Requirements:

Valid ID (front and back)
Proof of ownership/lease
Barangay clearance (optional but recommended)
Business permit (if applicable)
GCash number for receiving payments
Submit for admin approval


Admin Pages
14. Admin Dashboard

Platform statistics:

Total users (students, landlords)
Total listings
Total bookings
Total revenue (commissions)
Pending verifications


Recent activity feed
Quick actions

15. Landlord Verification Queue

List of pending verifications
View submitted documents
Approve/reject with notes
Send email notifications

16. Listings Management

All boarding houses
Filter by status, location, verification
Edit/deactivate listings
View analytics per listing

17. Transactions Report

All bookings and payments
Commission tracking
Filter by date range
Export to CSV
Outstanding commissions owed by landlords

18. User Management

All users (students and landlords)
Ban/suspend accounts
View user activity
Reset passwords

19. Reviews Moderation

Flag inappropriate reviews
Hide/remove reviews
Resolve disputes


💰 Payment Flow (Detailed)
Step-by-Step Booking & Payment Process:
1. Student Finds Boarding House

Browses/searches listings
Clicks "Book Now" on chosen property

2. Booking Form

Student selects move-in date
Reviews payment breakdown
Agrees to terms

3. Payment Processing

Student clicks "Pay with GCash"
Redirected to PayMongo/GCash checkout
Student completes payment (₱3,000 total example)
Payment goes directly to landlord's GCash

4. Payment Confirmation

System receives payment webhook from PayMongo
Booking status → "Paid - Pending Landlord Confirmation"
Email/SMS sent to both parties
Commission (₱90) recorded as owed by landlord

5. Landlord Confirmation

Landlord receives notification
After student moves in, landlord clicks "Confirm Move-In"
Booking status → "Active"

6. Commission Collection (Your End)
Monthly Commission Billing Process:

End of each month: Generate invoice for each landlord
Email landlords their commission statement
Landlords pay commission to your GCash
Mark as paid in system

OR Alternative - Per Booking:

When landlord confirms booking, prompt to pay commission
Landlord sends ₱90 to your GCash before confirmation
You verify payment and release booking confirmation


🔒 Verification & Trust Features
Landlord Verification Process:
1. Initial Registration

Landlord creates account
Redirected to verification page

2. Document Submission

Upload valid ID
Upload proof of ownership/lease
Upload barangay clearance
Provide GCash number
Submit for review

3. Admin Review

You manually review documents
Check if property address is real
Optional: Physical visit to property
Approve or reject with reason

4. Approval

Landlord notified via email
Can now post listings
"Verified" badge on their profile

Student Verification:
Email Verification:

Must register with @bipsu.edu.ph email
Verification code sent to email
Must verify within 24 hours
Cannot book until verified

Optional Additional Verification:

Upload student ID photo
Shows "Verified BIPSU Student" badge
Increases trust with landlords

Property Verification:
Photos Must Show:

Actual exterior of building
Common areas
Sample rooms
Bathrooms
Study areas

You Can:

Request additional photos if needed
Mark photos as "verified by admin"
Remove misleading listings


📱 User Flows
Student Booking Flow:
1. Student registers → Email verification
2. Search boarding houses → Apply filters
3. Click listing → View details & photos
4. Click "Book Now" → Select move-in date
5. Review payment → Pay via GCash
6. Payment success → Receive confirmation
7. Wait for landlord confirmation
8. Move in → Landlord confirms
9. After stay → Write review
Landlord Listing Flow:
1. Landlord registers
2. Submit verification documents
3. Wait for admin approval (you)
4. Approved → Create listing
5. Fill in details → Upload photos
6. Submit listing → Goes live
7. Receive booking notification
8. Student moves in → Confirm in system
9. Receive monthly rent via GCash
10. Pay monthly commission to platform
Admin Workflow:
Daily:
1. Check pending verifications → Approve/reject
2. Monitor new bookings
3. Respond to support inquiries
4. Moderate new reviews



sql code for the added features
sql 
users
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- email (VARCHAR, UNIQUE) - must be @bipsu.edu.ph for students
- password (VARCHAR, hashed)
- full_name (VARCHAR)
- contact_number (VARCHAR)
- user_type (ENUM: 'student', 'landlord', 'admin')
- email_verified (BOOLEAN, default FALSE)
- verification_token (VARCHAR)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
Landlords Table
sqllandlords
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_id (INT, FOREIGN KEY → users.id)
- business_name (VARCHAR)
- valid_id_url (VARCHAR) - photo of ID
- proof_of_ownership_url (VARCHAR) - property documents
- barangay_clearance_url (VARCHAR)
- verification_status (ENUM: 'pending', 'approved', 'rejected')
- verified_at (TIMESTAMP)
- gcash_number (VARCHAR) - for receiving payments
- commission_owed (DECIMAL)
- created_at (TIMESTAMP)
Boarding_Houses Table
sqlboarding_houses
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- landlord_id (INT, FOREIGN KEY → landlords.id)
- name (VARCHAR)
- address (TEXT)
- latitude (DECIMAL) - for map
- longitude (DECIMAL)
- monthly_rent (DECIMAL)
- security_deposit (DECIMAL)
- available_rooms (INT)
- total_rooms (INT)
- gender_allowed (ENUM: 'male', 'female', 'both')
- description (TEXT)
- house_rules (TEXT)
- curfew_time (TIME, nullable)
- status (ENUM: 'available', 'full', 'inactive')
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
Amenities Table
sqlamenities
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- boarding_house_id (INT, FOREIGN KEY → boarding_houses.id)
- wifi (BOOLEAN)
- own_cr (BOOLEAN)
- shared_kitchen (BOOLEAN)
- laundry_area (BOOLEAN)
- parking (BOOLEAN)
- study_area (BOOLEAN)
- air_conditioning (BOOLEAN)
- water_heater (BOOLEAN)
Photos Table
sqlphotos
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- boarding_house_id (INT, FOREIGN KEY → boarding_houses.id)
- photo_url (VARCHAR)
- is_primary (BOOLEAN) - main display photo
- uploaded_at (TIMESTAMP)
Bookings Table
sqlbookings
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- booking_reference (VARCHAR, UNIQUE) - e.g., BK-2025-0001
- student_id (INT, FOREIGN KEY → users.id)
- boarding_house_id (INT, FOREIGN KEY → boarding_houses.id)
- landlord_id (INT, FOREIGN KEY → landlords.id)
- move_in_date (DATE)
- monthly_rent (DECIMAL)
- security_deposit (DECIMAL)
- total_amount (DECIMAL)
- commission_amount (DECIMAL) - 3% of total
- payment_status (ENUM: 'pending', 'paid', 'refunded')
- payment_reference (VARCHAR) - GCash transaction ID
- booking_status (ENUM: 'pending', 'confirmed', 'active', 'completed', 'cancelled')
- landlord_confirmed_at (TIMESTAMP)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
Reviews Table
sqlreviews
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- booking_id (INT, FOREIGN KEY → bookings.id)
- student_id (INT, FOREIGN KEY → users.id)
- boarding_house_id (INT, FOREIGN KEY → boarding_houses.id)
- rating (INT) - 1 to 5
- cleanliness_rating (INT)
- location_rating (INT)
- value_rating (INT)
- landlord_rating (INT)
- comment (TEXT)
- landlord_response (TEXT, nullable)
- responded_at (TIMESTAMP, nullable)
- created_at (TIMESTAMP)
Transactions Table
sqltransactions
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- booking_id (INT, FOREIGN KEY → bookings.id)
- transaction_type (ENUM: 'booking_payment', 'commission_payment')
- amount (DECIMAL)
- payment_method (VARCHAR) - 'GCash'
- payment_reference (VARCHAR)
- status (ENUM: 'pending', 'completed', 'failed')
- processed_at (TIMESTAMP)
- created_at (TIMESTAMP)