-- =====================================================================
-- Primodomus Service Platform - Seed Data (Demo Environment)
-- NOTE: Demo data is explicitly marked as demo per prompt instructions.
-- Default Password for all demo accounts: password123
-- =====================================================================

-- 1. Initial Users (Admin, Technician, Customer)
INSERT INTO users (id, role, name, email, phone, password_hash, status, must_change_password) VALUES
(1, 'admin', 'System Administrator', 'admin@primodomus.com', '9876543210', '$2y$10$V21Wpw7.mv3a45OVT1nrc.CihUsOWwC/ve4.HaRwqXP.loXmoUeh.', 'active', 0),
(2, 'staff', 'Rajesh Sharma (Demo Tech)', 'tech@primodomus.com', '9876543211', '$2y$10$V21Wpw7.mv3a45OVT1nrc.CihUsOWwC/ve4.HaRwqXP.loXmoUeh.', 'active', 0),
(3, 'customer', 'Ananya Verma (Demo Customer)', 'customer@primodomus.com', '9876543212', '$2y$10$V21Wpw7.mv3a45OVT1nrc.CihUsOWwC/ve4.HaRwqXP.loXmoUeh.', 'active', 0);

-- 2. Customer Profile
INSERT INTO customer_profiles (user_id, address, city, pincode, lat, lng) VALUES
(3, 'Tower 4, Apt 802, Palm Springs, Sector 54', 'Gurugram', '122002', 28.43500000, 77.10500000);

-- 3. Staff Profile
INSERT INTO staff_profiles (user_id, rating_avg, rating_count, availability_note, is_available) VALUES
(2, 4.95, 24, 'Available for Gurugram & South Delhi visits', 1);

-- 4. Service Categories
INSERT INTO categories (id, name, slug, description, icon, sort_order, is_active) VALUES
(1, 'Cleaning', 'cleaning', 'Comprehensive deep cleaning solutions for residential and commercial spaces.', 'refixel-cleaning.jpg', 1, 1),
(2, 'Painting Services', 'painting-services', 'Professional interior and exterior painting with dust-free mechanized tools.', 'refixel-painting.jpg', 2, 1),
(3, 'Fall Ceiling', 'fall-ceiling-services', 'Architectural Gypsum & POP fall ceiling solutions, precision laser leveling, LED cove light troughs, and seamless crack-free finishing by Refixel specialists.', 'refixel-fall-ceiling.jpg', 3, 1),
(4, 'Plumbers', 'plumbers', 'Expert plumbers for leak fixes, tap fittings, pipe blocks, and sanitary ware.', 'refixel-plumber.jpg', 4, 1),
(5, 'Carpenter', 'carpenter', 'Skilled carpentry services for furniture repair, assembly, and bespoke woodwork.', 'refixel-carpenter.jpg', 5, 1),
(6, 'AC Service & Repair', 'ac-services', 'High-pressure jet servicing, gas charging, filter sanitization, and cooling diagnostics.', 'refixel-ac-service.jpg', 6, 1),
(7, 'Electrician', 'electrician', 'Professional electrician services for wiring, switchboards, MCBs, fans, and appliance installations.', 'refixel-electrician.jpg', 7, 1),
(8, 'Appliance Repair', 'appliance-repair', 'Expert repair and servicing for washing machines, refrigerators, microwaves, and home appliances.', 'refixel-appliance-repair.jpg', 8, 1),
(9, 'Pest Control', 'pest-control', 'Eco-friendly and odourless pest control solutions for cockroaches, termites, and pests.', 'service-pest-control.jpg', 9, 1);

-- 5. Staff Skills
INSERT INTO staff_skills (staff_id, category_id) VALUES
(2, 1), -- Rajesh handles Cleaning
(2, 6); -- Rajesh handles AC Service

-- 6. Staff Availability (Monday to Saturday, 9 AM to 7 PM)
INSERT INTO staff_availability (staff_id, weekday, start_time, end_time) VALUES
(2, 1, '09:00:00', '19:00:00'),
(2, 2, '09:00:00', '19:00:00'),
(2, 3, '09:00:00', '19:00:00'),
(2, 4, '09:00:00', '19:00:00'),
(2, 5, '09:00:00', '19:00:00'),
(2, 6, '09:00:00', '19:00:00');

-- 7. Services
INSERT INTO services (id, category_id, name, slug, description, starting_price, duration_minutes, image, is_active) VALUES
(1, 1, 'Professional Full Home Cleaning', 'full-home-cleaning', 'Deep scrubbing, floor buffing, dust removal, kitchen degreasing, and sanitized bathrooms.', 2499.00, 240, 'refixel-cleaning.jpg', 1),
(2, 1, 'Professional Bathroom Cleaning', 'bathroom-deep-cleaning', 'Intensive tile descaling, toilet scrubbing, fittings stain removal, and sanitization.', 499.00, 60, 'refixel-bathroom-cleaning.jpg', 1),
(3, 1, 'Kitchen Deep Cleaning', 'kitchen-deep-cleaning', 'Heavy oil degreasing, chimney exterior scrubbing, tile cleaning, and cabinets wiping.', 999.00, 120, 'refixel-kitchen-cleaning.jpg', 1),
(4, 1, 'Sofa & Upholstery Deep Cleaning', 'sofa-cleaning', 'Mechanized fabric shampooing, stain extraction, and high-suction vacuuming.', 799.00, 90, 'refixel-sofa-cleaning.jpg', 1),
(5, 1, 'Commercial Space & Office Cleaning', 'office-cleaning', 'Floor disinfection, workstation sanitizing, carpet cleaning, and pantry maintenance.', 3499.00, 300, 'refixel-office-cleaning.jpg', 1),
(6, 2, 'Interior Home Painting', 'interior-painting', 'Laser measurements, automated sanding, primer coating, and premium emulsion paint application.', 4999.00, 480, 'Painting-Services.png', 1),
(7, 9, 'Cockroach & Ant Pest Control', 'cockroach-pest-control', 'Gel baiting technology and odorless spray treatment across all corners.', 799.00, 45, 'Cockroach-Ant -Pest-Control -Services.webp', 1),
(8, 4, 'Tap & Pipe Leak Repair', 'tap-leak-repair', 'Instant leak detection, washer replacement, and tight seal fittings.', 299.00, 45, 'Plumber.webp', 1),
(9, 5, 'Furniture Assembly & Wood Repair', 'furniture-assembly', 'Expert carpenter visit for bed, table, wardrobe assembly, and hinge repairs.', 399.00, 60, 'Carpenter.webp', 1),
(10, 6, 'AC High-Pressure Jet Service', 'ac-jet-service', 'Deep jet cleaning of indoor cooling coils and outdoor units for maximum airflow and cooling.', 599.00, 60, 'AC-Services.webp', 1),
(11, 7, 'Fan & Switchboard Repair', 'fan-switchboard-repair', 'Fixing switches, ceiling fans, sockets, wiring faults and circuit breakers.', 199.00, 45, 'repair.webp', 1),
(13, 3, 'Designer Fall Ceiling & POP Installation', 'fall-ceiling-installation', 'End-to-end false ceiling design & installation with heavy GI steel channel framing, branded Saint-Gobain gypsum boards, laser alignment, concealed LED cove light provision, and crack-free joint tape plastering by Refixel interior experts.', 1499.00, 180, 'refixel-fall-ceiling.jpg', 1),
(14, 3, 'Fall Ceiling Repair & Cove Light Modification', 'fall-ceiling-repair-modification', 'Precision repair of sagging, damp, or cracked POP/gypsum ceiling panels, joint re-taping, acoustic leveling, and cutting custom slots for profile lights and spotlights.', 699.00, 90, 'refixel-fall-ceiling.jpg', 1);

-- 8. Service Checklist Items
INSERT INTO service_checklist_items (service_id, label, is_included, sort_order) VALUES
(1, 'Mechanized single-disc floor buffing and scrubbing', 1, 1),
(1, 'Bathroom tile descaling and mirror polishing', 1, 2),
(11, 'Switch & socket diagnostic check', 1, 1),
(11, 'Safe insulated testing and circuit isolation', 1, 2),
(1, 'Kitchen cabinet interior and exterior degreasing', 1, 3),
(1, 'Balcony, grills, and window glass wiping', 1, 4),
(1, 'Wall painting touch-ups or civil work', 0, 5),
(1, 'Moving heavy furniture exceeding 40kg without assistance', 0, 6),
(2, 'Hard water stain removal from shower glass and tiles', 1, 1),
(2, 'Sanitization of WC, washbasin, and vanity mirrors', 1, 2),
(2, 'Grout scrubbing and chrome fittings shining', 1, 3),
(2, 'Bathroom ceiling replastering or plumbing structural replacement', 0, 4),
(10, 'Indoor blower and cooling coil high-pressure jet wash', 1, 1),
(10, 'Outdoor condenser coil cleaning and drain tray wash', 1, 2),
(10, 'Gas pressure check and operating amp verification', 1, 3),
(10, 'Gas refilling charges (billed separately if leakage found)', 0, 4);

-- 9. Service Areas (Major Cities & Pincodes)
INSERT INTO service_areas (city, area_name, pincode, is_active) VALUES
('Gurugram', 'Cyber City / DLF Phase 2', '122002', 1),
('Gurugram', 'Golf Course Road / Sector 54', '122011', 1),
('Gurugram', 'Sohna Road / Sector 48', '122018', 1),
('Delhi-NCR', 'South Extension', '110049', 1),
('Delhi-NCR', 'Vasant Kunj', '110070', 1),
('Delhi-NCR', 'Connaught Place', '110001', 1),
('Delhi-NCR', 'Dwarka Sector 10', '110075', 1),
('Delhi-NCR', 'Noida Sector 62', '201301', 1),
('Mumbai', 'Bandra West', '400050', 1),
('Mumbai', 'Andheri East', '400069', 1),
('Hyderabad', 'Hitec City', '500081', 1),
('Chennai', 'Anna Nagar', '600040', 1),
('Ahmedabad', 'Bodakdev', '380054', 1),
('Chandigarh', 'Sector 17', '160017', 1),
('Kochi', 'Marine Drive', '682031', 1),
('Pune', 'Koregaon Park', '411001', 1);

-- 10. Process Steps
INSERT INTO process_steps (step_no, title, description, is_active) VALUES
(1, 'Book Online', 'Select your required service, pick a preferred date and time slot, and enter your address.', 1),
(2, 'Expert Assigned', 'A background-verified technician is matched and assigned to your booking.', 1),
(3, 'Doorstep Visit & Service', 'Technician arrives on schedule equipped with specialized tools and industrial consumables.', 1),
(4, 'Inspection & Invoice', 'Inspect the completed work, pay securely via Cash/UPI, and receive a formal GST receipt.', 1);

-- 11. Frequently Asked Questions (Global & Specific)
INSERT INTO faqs (service_id, question, answer, sort_order, is_active) VALUES
(NULL, 'How do I book a service on Primodomus?', 'Select your desired service, choose your preferred date and time slot, enter your address, and confirm your request. You will receive an immediate booking confirmation.', 1, 1),
(NULL, 'Are the service professionals background-checked?', 'Yes. All technicians and service partners undergo identity verification, police background checks, and practical skills assessments before onboarding.', 2, 1),
(NULL, 'What payment methods are supported?', 'We accept UPI (Google Pay, PhonePe, Paytm), Net Banking, Credit/Debit cards, and Cash on Delivery upon satisfactory job completion.', 3, 1),
(NULL, 'Can I reschedule or cancel my booking?', 'Yes. You can cancel or reschedule your booking free of charge up to 2 hours prior to the scheduled technician arrival time.', 4, 1),
(1, 'What is included in the Full Home Cleaning service?', 'Full Home Cleaning covers thorough scrubbing of all rooms, kitchen degreasing, bathroom descaling, window sill and glass wiping, balcony cleaning, and mechanized floor buffing.', 1, 1),
(10, 'How often should an air conditioner be jet serviced?', 'We recommend deep jet servicing at least twice a year—once before the summer season begins and once mid-season—to maintain peak cooling efficiency and lower power consumption.', 1, 1);

-- 12. Site Settings
INSERT INTO settings (setting_key, setting_value) VALUES
('company_name', 'Primodomus Home Services'),
('company_phone', '+91 99999 99999'),
('company_email', 'help@primodomus.com'),
('company_address', 'Cyber City, DLF Phase 2, Gurugram, Haryana 122002'),
('company_gstin', '07AAAAA0000A1Z5'),
('promo_strip_text', 'Starting at ₹999 • Save up to 25% on your first booking'),
('stat_rating', '4.8★'),
('stat_rating_note', 'Rated by 1000+ Happy Customers'),
('stat_homes_cleaned', '5000+'),
('stat_homes_note', 'Homes Professionally Cleaned'),
('stat_service_partners', '60+'),
('stat_partners_note', 'Trusted Service Partners'),
('stat_verified_pros', '400+'),
('stat_pros_note', 'Verified Professionals');

-- 13. Demo Booking & Assigned Job (Clearly marked as Demo)
INSERT INTO bookings (id, booking_no, customer_id, service_id, name, phone, email, address, pincode, preferred_date, preferred_time, issue_details, status, priority, created_at) VALUES
(1, 'BK-DEMO-001', 3, 1, 'Ananya Verma (Demo Customer)', '9876543212', 'customer@primodomus.com', 'Tower 4, Apt 802, Palm Springs, Sector 54, Gurugram', '122002', CURDATE(), '10:00 AM - 01:00 PM', 'Complete deep cleaning before housewarming party.', 'assigned', 'high', NOW());

-- 14. Demo Job linked to Staff Rajesh Sharma
INSERT INTO jobs (id, booking_id, staff_id, status, scheduled_at, created_at) VALUES
(1, 1, 2, 'assigned', NOW(), NOW());

-- 15. Initial Status History for Demo Job
INSERT INTO status_history (job_id, from_status, to_status, changed_by, notes, created_at) VALUES
(1, 'new', 'assigned', 1, 'Assigned technician Rajesh Sharma to booking BK-DEMO-001', NOW());

-- 16. Before / After Gallery Items Showcase
INSERT INTO gallery_items (id, service_id, title, before_image, after_image, sort_order, is_active) VALUES
(1, 1, 'Laundry & Room Deep Cleaning', 'assets/img/before-after-laundry-cleaning.png', 'assets/img/before-after-laundry-cleaning.png', 1, 1),
(2, 7, 'Kitchen Pest Control & Roach Eradication', 'assets/img/before-after-pest-control.png', 'assets/img/before-after-pest-control.png', 2, 1),
(3, 4, 'Sofa & Upholstery Deep Cleaning', 'assets/img/before-after-sofa-cleaning.png', 'assets/img/before-after-sofa-cleaning.png', 3, 1),
(4, 6, 'Living Room Wall Painting & Refurbishment', 'assets/img/before-after-room-painting.png', 'assets/img/before-after-room-painting.png', 4, 1),
(5, 9, 'TV Feature Wall & Carpentry Renovation', 'assets/img/before-after-wall-renovation.png', 'assets/img/before-after-wall-renovation.png', 5, 1),
(6, 11, 'Electrical MCB Panel & Switchboard Overhaul', 'assets/img/before-after-electrical-repair.png', 'assets/img/before-after-electrical-repair.png', 6, 1),
(7, 8, 'Under-Sink Pipe Leak & Drainage Repair', 'assets/img/before-after-plumbing-repair.png', 'assets/img/before-after-plumbing-repair.png', 7, 1),
(8, 10, 'AC Jet Service & Cooling Coil Deep Cleansing', 'assets/img/before-after-ac-service.png', 'assets/img/before-after-ac-service.png', 8, 1);


