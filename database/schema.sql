-- =====================================================================
-- Primodomus Service Platform - Production Database Schema
-- MySQL 8.0+ / MariaDB 10.6+ Compatible
-- Character Set: utf8mb4, Collation: utf8mb4_unicode_ci
-- Engine: InnoDB strictly across all tables
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS consents;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS process_steps;
DROP TABLE IF EXISTS gallery_items;
DROP TABLE IF EXISTS faqs;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS staff_earnings;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS status_history;
DROP TABLE IF EXISTS job_photos;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS booking_attachments;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS service_areas;
DROP TABLE IF EXISTS service_checklist_items;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS staff_availability;
DROP TABLE IF EXISTS staff_skills;
DROP TABLE IF EXISTS staff_profiles;
DROP TABLE IF EXISTS customer_profiles;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users Table (Core authentication registry for all three roles)
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role ENUM('customer','admin','staff') NOT NULL,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NULL UNIQUE,
    phone VARCHAR(15) NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active','disabled') NOT NULL DEFAULT 'active',
    must_change_password TINYINT(1) NOT NULL DEFAULT 0,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role_status (role, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Customer Profiles
CREATE TABLE customer_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    house_no VARCHAR(100) NULL,
    street VARCHAR(255) NULL,
    address VARCHAR(255) NULL,
    city VARCHAR(80) NULL,
    state VARCHAR(100) NULL,
    address_type VARCHAR(50) DEFAULT 'Home',
    pincode VARCHAR(10) NULL,
    lat DECIMAL(10,8) NULL,
    lng DECIMAL(11,8) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2b. Multiple Customer Addresses
CREATE TABLE IF NOT EXISTS customer_addresses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    label VARCHAR(80) NOT NULL DEFAULT 'Home',
    house_no VARCHAR(100) NULL,
    street VARCHAR(255) NULL,
    address VARCHAR(255) NULL,
    city VARCHAR(80) NULL,
    state VARCHAR(100) NULL,
    pincode VARCHAR(10) NULL,
    address_type VARCHAR(50) DEFAULT 'Home',
    lat DECIMAL(10,8) NULL,
    lng DECIMAL(11,8) NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Staff / Technician Profiles
CREATE TABLE staff_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    rating_avg DECIMAL(3,2) NOT NULL DEFAULT 5.00,
    rating_count INT UNSIGNED NOT NULL DEFAULT 0,
    availability_note VARCHAR(255) NULL DEFAULT 'Available',
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Service Categories
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    icon VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cat_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Staff Skills (Mapping technicians to service categories)
CREATE TABLE staff_skills (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_staff_skill (staff_id, category_id),
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Staff Working Availability Slots
CREATE TABLE staff_availability (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id INT UNSIGNED NOT NULL,
    weekday TINYINT UNSIGNED NOT NULL COMMENT '0=Sunday, 6=Saturday',
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Services Catalogue
CREATE TABLE services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(140) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    description TEXT NULL,
    starting_price DECIMAL(10,2) NOT NULL,
    duration_minutes SMALLINT UNSIGNED NULL,
    image VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_svc_cat_active (category_id, is_active),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Service Checklist Items (Inclusions / Exclusions)
CREATE TABLE service_checklist_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id INT UNSIGNED NOT NULL,
    label VARCHAR(255) NOT NULL,
    is_included TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Service Areas & Pincodes
CREATE TABLE service_areas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(80) NOT NULL,
    area_name VARCHAR(120) NULL,
    pincode VARCHAR(10) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    INDEX idx_city (city),
    INDEX idx_pincode (pincode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Bookings / Enquiries
CREATE TABLE bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_no VARCHAR(20) NOT NULL UNIQUE,
    customer_id INT UNSIGNED NULL,
    service_id INT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(160) NULL,
    address VARCHAR(255) NOT NULL,
    pincode VARCHAR(10) NULL,
    preferred_date DATE NULL,
    preferred_time VARCHAR(30) NULL,
    issue_details TEXT NULL,
    status ENUM('new','assigned','accepted','in_progress','completed','invoiced','reviewed','cancelled','rescheduled','refund_requested','refunded') NOT NULL DEFAULT 'new',
    priority ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
    admin_notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status_created (status, created_at),
    INDEX idx_customer (customer_id),
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Customer Issue Attachments
CREATE TABLE booking_attachments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    mime VARCHAR(60) NOT NULL,
    size INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Jobs (Assigned Field Operations)
CREATE TABLE jobs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL UNIQUE,
    staff_id INT UNSIGNED NOT NULL,
    status ENUM('assigned','accepted','in_progress','completed') NOT NULL DEFAULT 'assigned',
    scheduled_at DATETIME NULL,
    accepted_at DATETIME NULL,
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    work_summary TEXT NULL,
    final_notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_staff_status (staff_id, status),
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Job Photos (Before and After Service Images)
CREATE TABLE job_photos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL,
    type ENUM('before','after') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Status Audit History
CREATE TABLE status_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL,
    from_status VARCHAR(40) NOT NULL,
    to_status VARCHAR(40) NOT NULL,
    changed_by INT UNSIGNED NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Payments Records
CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method ENUM('cash','upi','card','bank') NULL,
    status ENUM('pending','partial','paid','refunded') NOT NULL DEFAULT 'pending',
    transaction_ref VARCHAR(100) NULL,
    paid_at DATETIME NULL,
    recorded_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Invoices (GST-Compliant Records)
CREATE TABLE invoices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id INT UNSIGNED NOT NULL UNIQUE,
    invoice_no VARCHAR(30) NOT NULL UNIQUE,
    subtotal DECIMAL(10,2) NOT NULL,
    gst_rate DECIMAL(4,2) NOT NULL DEFAULT 18.00,
    gst_amount DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    issued_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Technician Earnings
CREATE TABLE staff_earnings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL,
    staff_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    gross_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    material_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    admin_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    month VARCHAR(7) NOT NULL COMMENT 'YYYY-MM',
    is_settled TINYINT(1) NOT NULL DEFAULT 0,
    settled_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_staff_month (staff_id, month),
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Customer Reviews & Ratings
CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL UNIQUE,
    customer_id INT UNSIGNED NOT NULL,
    staff_id INT UNSIGNED NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NULL,
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_approved (is_approved),
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Frequently Asked Questions (Global and Per-Service)
CREATE TABLE faqs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id INT UNSIGNED NULL,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    INDEX idx_svc_faq (service_id, is_active),
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. Before/After Gallery Showcase
CREATE TABLE gallery_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id INT UNSIGNED NULL,
    title VARCHAR(140) NOT NULL,
    before_image VARCHAR(255) NOT NULL,
    after_image VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. Service Workflow Steps (Public Display)
CREATE TABLE process_steps (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    step_no TINYINT UNSIGNED NOT NULL UNIQUE,
    title VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. Site-wide Key/Value Settings
CREATE TABLE settings (
    setting_key VARCHAR(64) PRIMARY KEY,
    setting_value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. Outgoing Notifications Log
CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    channel ENUM('email','sms','whatsapp') NOT NULL,
    to_address VARCHAR(160) NOT NULL,
    template VARCHAR(80) NOT NULL,
    payload JSON NULL,
    status ENUM('pending','sent','failed') NOT NULL DEFAULT 'sent',
    error_message TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_channel_status (channel, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. Password Resets
CREATE TABLE password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email_or_phone VARCHAR(160) NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25. Login Attempts (Brute Force Monitoring)
CREATE TABLE login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    identifier VARCHAR(160) NOT NULL,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_attempt (ip, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 26. User Consents (DPDP Act Compliance Record)
CREATE TABLE consents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    purpose VARCHAR(100) NOT NULL,
    granted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(45) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 27. Payment Gateways Registry
CREATE TABLE payment_gateways (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    environment ENUM('test','live') NOT NULL DEFAULT 'live',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    key_id VARCHAR(255) NULL,
    key_secret VARCHAR(255) NULL,
    webhook_secret VARCHAR(255) NULL,
    merchant_email VARCHAR(150) NULL,
    merchant_name VARCHAR(150) NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'INR',
    description TEXT NULL,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 28. Commission Rules & Profit Splits
CREATE TABLE commission_rules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NULL,
    worker_tier ENUM('all','senior','standard','trainee') NOT NULL DEFAULT 'all',
    admin_percent DECIMAL(5,2) NOT NULL DEFAULT 25.00,
    worker_percent DECIMAL(5,2) NOT NULL DEFAULT 75.00,
    deduct_material_first TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Deduct material cost before calculating split',
    material_payer ENUM('worker','company','customer') NOT NULL DEFAULT 'worker' COMMENT 'Party providing materials upfront',
    min_visit_charge DECIMAL(10,2) NOT NULL DEFAULT 200.00,
    notes VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
