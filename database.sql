-- ============================================================================
-- Barangay Treasurer Management System Database Schema
-- Barangay Sto. Rosario, Magallanes, Agusan del Norte
-- ============================================================================

-- Create the database
CREATE DATABASE IF NOT EXISTS treasurer_management;
USE treasurer_management;

-- ============================================================================
-- 1. USERS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(150) UNIQUE DEFAULT NULL,
    email_verified_at DATETIME DEFAULT NULL,
    email_verification_token VARCHAR(64) DEFAULT NULL,
    email_verification_expires_at DATETIME DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(50) DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================================
-- 1.1 PASSWORD RESETS TABLE
-- Stores OTP codes for forgot-password flow
-- ============================================================================
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_password_resets_user_id ON password_resets (user_id);
CREATE INDEX idx_password_resets_expires_at ON password_resets (expires_at);

-- ============================================================================
-- 2. PAYMENTS TABLE
-- Stores all payment transactions (clearances, permits, etc.)
-- ============================================================================
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_no VARCHAR(50) NOT NULL,
    payment_date DATE NOT NULL,
    payer_name VARCHAR(150) NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    purpose VARCHAR(255) NOT NULL,
    operating_services VARCHAR(100),  
    amount DECIMAL(10, 2) NOT NULL,
    bir_tax DECIMAL(10, 2) DEFAULT 0,
    remarks TEXT,
    received_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ==========================================================================
-- 2.1 PAYMENT STATUS TABLE
-- Stores pending payments before they are marked as paid
-- ==========================================================================
CREATE TABLE IF NOT EXISTS payment_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_type VARCHAR(100) NOT NULL,
    purpose VARCHAR(255) NOT NULL,
    resident_fname VARCHAR(150) NOT NULL,
    payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    amount DECIMAL(10, 2) NOT NULL,
    bir_tax DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- 3. CEDULA TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS cedula (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula_no VARCHAR(50) NOT NULL,
    or_number VARCHAR(50),  
    issued_date DATE NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    address TEXT NOT NULL,
    birth_date DATE,
    age INT,
    sex ENUM('Male', 'Female'),
    birth_place VARCHAR(100),
    civil_status VARCHAR(20),
    citizenship VARCHAR(100) DEFAULT 'Filipino',
    occupation VARCHAR(100),
    tin VARCHAR(50),
    height DECIMAL(5, 2),
    weight DECIMAL(5, 2),
    annual_income DECIMAL(12, 2) DEFAULT 0.00,
    amount DECIMAL(10, 2) NOT NULL,
    nature_of_collection VARCHAR(100) DEFAULT 'Community Tax',  
    remarks TEXT,
    issued_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================================
-- 4. BIR RECORDS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS bir_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tin VARCHAR(50) NOT NULL,
    payee VARCHAR(150) NOT NULL,
    vat_type VARCHAR(20) DEFAULT 'Non-VAT',
    record_date DATE NOT NULL,
    gross_amount DECIMAL(12, 2) NOT NULL COMMENT 'Total amount paid (includes taxes)',
    one_percent DECIMAL(10, 2) NOT NULL COMMENT '1% withholding tax',
    five_percent DECIMAL(10, 2) NOT NULL COMMENT '5% withholding tax',
    total_amount DECIMAL(12, 2) NOT NULL COMMENT 'Total withholding (1% + 5%)',
    net_amount DECIMAL(12, 2) NOT NULL COMMENT 'Net amount to payee (gross - total_amount)',
    remarks TEXT,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================================
-- 5. DISBURSEMENTS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS disbursements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    disburse_date DATE NOT NULL,
    check_no VARCHAR(50) NOT NULL,
    or_no VARCHAR(50),
    received_date DATE,
    payee VARCHAR(150) NOT NULL,
    payee_address TEXT,
    payee_tin VARCHAR(50),
    dv_no VARCHAR(50) NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    fund VARCHAR(100),
    payroll VARCHAR(100),
    bir VARCHAR(100),
    purpose TEXT NOT NULL,
    release_amount DECIMAL(12, 2) NOT NULL,
    accounting_entries TEXT,
    signatory_a VARCHAR(150),
    signatory_b VARCHAR(150),
    signatory_c VARCHAR(150),
    signatory_prepared_by VARCHAR(150),
    signatory_checked_by VARCHAR(150),
    signatory_approved_by VARCHAR(150),
    signatory_received_by VARCHAR(150),
    processed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================================
-- 6. MONTHLY MANUAL ENTRIES TABLE
-- Stores manually inputted values for monthly collections report
-- ============================================================================
CREATE TABLE IF NOT EXISTS monthly_manual_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    month INT NOT NULL,
    year INT NOT NULL,
    entry_name VARCHAR(200) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    entry_type VARCHAR(100), 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_entry (month, year, entry_name)
);

-- ============================================================================
-- DEFAULT USER & SAMPLE DATA
-- Username: treasurer | Password: treasurer123 (MD5 hash)
-- ============================================================================

-- Insert default treasurer user
INSERT INTO users (username, email, email_verified_at, password, name, role) VALUES
('treasurer', 'treasurer@example.com', NOW(), MD5('treasurer123'), 'Barangay Treasurer', 'treasurer')
ON DUPLICATE KEY UPDATE password = MD5('treasurer123');

-- Insert default admin user (same access as treasurer)
INSERT INTO users (username, email, email_verified_at, password, name, role) VALUES
('admin', 'admin@example.com', NOW(), MD5('admin123'), 'System Administrator', 'admin')
ON DUPLICATE KEY UPDATE password = MD5('admin123');

-- ============================================================================
-- SAMPLE PAYMENTS DATA
-- ============================================================================
INSERT INTO payments (receipt_no, payment_date, payer_name, service_type, purpose, operating_services, amount, bir_tax, remarks, received_by) VALUES
('OR-2026-001', '2026-01-15', 'Juan Dela Cruz', 'Barangay Clearance', 'Barangay Clearance for Employment', 'Barangay Clearance', 150.00, 0, 'For overseas employment', 1),
('OR-2026-002', '2026-01-16', 'Maria Santos', 'Barangay Clearance', 'Barangay Clearance for Business Permit', 'Barangay Clearance', 150.00, 0, 'Sari-sari store permit', 1),
('OR-2026-003', '2026-01-17', 'Pedro Reyes', 'Business Permit', 'Business Permit Renewal', 'Business Permit Fee', 500.00, 0, 'Carinderia business', 1),
('OR-2026-004', '2026-01-18', 'Ana Garcia', 'Barangay ID', 'Barangay ID Issuance', 'ID Processing Fee', 50.00, 0, 'New resident', 1),
('OR-2026-005', '2026-01-19', 'Roberto Cruz', 'Barangay Clearance', 'Barangay Clearance for Loan Application', 'Barangay Clearance', 150.00, 0, 'Bank loan requirement', 1);

-- ============================================================================
-- SAMPLE CEDULA DATA
-- ============================================================================
INSERT INTO cedula (cedula_no, or_number, issued_date, full_name, address, birth_date, age, sex, birth_place, civil_status, occupation, tin, height, weight, amount, nature_of_collection, remarks, issued_by) VALUES
('CTC-2026-001', 'OR-2026-101', '2026-01-15', 'Juan Dela Cruz', 'Purok 1, Sto. Rosario, Magallanes, Agusan del Norte', '1985-05-12', 40, 'Male', 'Butuan City', 'Married', 'Driver', '123-456-789-000', 1.70, 70.00, 35.00, 'Community Tax', 'Regular cedula', 1),
('CTC-2026-002', 'OR-2026-102', '2026-01-16', 'Maria Santos', 'Purok 2, Sto. Rosario, Magallanes, Agusan del Norte', '1990-08-22', 35, 'Female', 'Magallanes', 'Single', 'Teacher', '234-567-890-000', 1.60, 55.00, 30.00, 'Community Tax', 'For employment', 1),
('CTC-2026-003', 'OR-2026-103', '2026-01-17', 'Pedro Reyes', 'Purok 3, Sto. Rosario, Magallanes, Agusan del Norte', '1978-03-15', 47, 'Male', 'Butuan City', 'Married', 'Businessman', '345-678-901-000', 1.75, 80.00, 50.00, 'Community Tax', 'Business owner', 1),
('CTC-2026-004', 'OR-2026-104', '2026-01-18', 'Ana Garcia', 'Purok 4, Sto. Rosario, Magallanes, Agusan del Norte', '1995-11-30', 30, 'Female', 'Magallanes', 'Married', 'Housewife', '456-789-012-000', 1.58, 52.00, 25.00, 'Community Tax', 'Regular', 1),
('CTC-2026-005', 'OR-2026-105', '2026-01-19', 'Roberto Cruz', 'Purok 5, Sto. Rosario, Magallanes, Agusan del Norte', '1988-07-08', 37, 'Male', 'Butuan City', 'Single', 'Farmer', '567-890-123-000', 1.68, 65.00, 30.00, 'Community Tax', 'Regular cedula', 1);

-- ============================================================================
-- SAMPLE BIR RECORDS DATA
-- ============================================================================
INSERT INTO bir_records (tin, payee, record_date, gross_amount, one_percent, five_percent, total_amount, net_amount, remarks, recorded_by) VALUES
('900-123-456-000', 'Uncle Ben Meatshop', '2026-01-15', 2062.00, 18.41, 92.05, 110.46, 1951.54, 'Meat supplies for barangay feeding program', 1),
('900-234-567-000', 'ABC Hardware Supply', '2026-01-16', 5000.00, 44.64, 223.21, 267.85, 4732.15, 'Construction materials for barangay hall repair', 1),
('900-345-678-000', 'XYZ Office Supplies', '2026-01-17', 3500.00, 31.25, 156.25, 187.50, 3312.50, 'Office supplies and equipment', 1);

-- ============================================================================
-- SAMPLE DISBURSEMENTS DATA
-- ============================================================================
INSERT INTO disbursements (
    disburse_date,
    check_no,
    or_no,
    received_date,
    payee,
    payee_address,
    payee_tin,
    dv_no,
    amount,
    fund,
    payroll,
    bir,
    purpose,
    release_amount,
    accounting_entries,
    signatory_a,
    signatory_b,
    signatory_c,
    signatory_prepared_by,
    signatory_checked_by,
    signatory_approved_by,
    signatory_received_by,
    processed_by
) VALUES
('2026-01-15', 'CHK-001-2026', 'OR-2026-201', '2026-01-15', 'Juan Dela Cruz', 'Purok 1, Sto. Rosario, Magallanes', '123-456-789-000', 'DV-2026-001', 5000.00, 'General Fund', 'Salary - January', '110.46', 'Salary payment for Barangay Tanod', 4889.54, 'Advances for payroll|1-03-05-020|5000.00|0', 'Margie M. Gabato', 'Paz A. Bacolod', 'Cleopatra R. Roces', 'Paz A. Bacolod', 'Cristhel Rhea S. Dela Fuerta', 'Aura R. Cadenas, CPA', 'Juan Dela Cruz', 1),
('2026-01-16', 'CHK-002-2026', 'OR-2026-202', '2026-01-16', 'Uncle Ben Meatshop', 'Purok 2, Sto. Rosario, Magallanes', '900-123-456-000', 'DV-2026-002', 2062.00, 'Special Fund', '', '110.46', 'Payment for meat supplies', 1951.54, '', 'Margie M. Gabato', 'Paz A. Bacolod', 'Cleopatra R. Roces', 'Paz A. Bacolod', 'Cristhel Rhea S. Dela Fuerta', 'Aura R. Cadenas, CPA', 'Uncle Ben Meatshop', 1),
('2026-01-17', 'CHK-003-2026', 'OR-2026-203', '2026-01-17', 'ABC Hardware Supply', 'Purok 3, Sto. Rosario, Magallanes', '900-234-567-000', 'DV-2026-003', 5000.00, 'General Fund', '', '267.85', 'Construction materials', 4732.15, '', 'Margie M. Gabato', 'Paz A. Bacolod', 'Cleopatra R. Roces', 'Paz A. Bacolod', 'Cristhel Rhea S. Dela Fuerta', 'Aura R. Cadenas, CPA', 'ABC Hardware Supply', 1),
('2026-01-18', 'CHK-004-2026', 'OR-2026-204', '2026-01-18', 'Maria Santos', 'Purok 4, Sto. Rosario, Magallanes', '234-567-890-000', 'DV-2026-004', 8000.00, 'General Fund', 'Salary - January', '180.00', 'Salary payment for Barangay Secretary', 7820.00, '', 'Margie M. Gabato', 'Paz A. Bacolod', 'Cleopatra R. Roces', 'Paz A. Bacolod', 'Cristhel Rhea S. Dela Fuerta', 'Aura R. Cadenas, CPA', 'Maria Santos', 1),
('2026-01-19', 'CHK-005-2026', 'OR-2026-205', '2026-01-19', 'PLDT Home', 'Purok 5, Sto. Rosario, Magallanes', '567-890-123-000', 'DV-2026-005', 1500.00, 'General Fund', '', '', 'Internet and telephone bills', 1500.00, '', 'Margie M. Gabato', 'Paz A. Bacolod', 'Cleopatra R. Roces', 'Paz A. Bacolod', 'Cristhel Rhea S. Dela Fuerta', 'Aura R. Cadenas, CPA', 'PLDT Home', 1);

-- ============================================================================
-- SAMPLE MONTHLY MANUAL ENTRIES DATA
-- ============================================================================
INSERT INTO monthly_manual_entries (month, year, entry_name, amount, entry_type) VALUES
(1, 2026, 'Share on Real Property Tax', 15000.00, 'Tax Revenue'),
(1, 2026, 'Share on Internal Revenue Allotment', 50000.00, 'Tax on Goods & Services'),
(1, 2026, 'National Tax Allotment', 25000.00, 'Tax on Goods & Services'),
(1, 2026, 'Service Income - Xerox', 500.00, 'Operating & Services'),
(1, 2026, 'Donations and Grants', 10000.00, 'Other'),
(1, 2026, 'Miscellaneous Income', 2000.00, 'Other');

