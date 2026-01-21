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
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(50) DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

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
    occupation VARCHAR(100),
    tin VARCHAR(50),
    height DECIMAL(5, 2),
    weight DECIMAL(5, 2),
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
    payee VARCHAR(150) NOT NULL,
    dv_no VARCHAR(50) NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    fund VARCHAR(100),
    payroll VARCHAR(100),
    bir VARCHAR(100),
    purpose TEXT NOT NULL,
    release_amount DECIMAL(12, 2) NOT NULL,
    remarks TEXT,
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
INSERT INTO users (username, password, name, role) VALUES
('treasurer', MD5('treasurer123'), 'Barangay Treasurer', 'treasurer')
ON DUPLICATE KEY UPDATE password = MD5('treasurer123');

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
INSERT INTO disbursements (disburse_date, check_no, payee, dv_no, amount, fund, payroll, bir, purpose, release_amount, remarks, processed_by) VALUES
('2026-01-15', 'CHK-001-2026', 'Juan Dela Cruz', 'DV-2026-001', 5000.00, 'General Fund', 'Salary - January', '110.46', 'Salary payment for Barangay Tanod', 4889.54, 'With withholding tax', 1),
('2026-01-16', 'CHK-002-2026', 'Uncle Ben Meatshop', 'DV-2026-002', 2062.00, 'Special Fund', '', '110.46', 'Payment for meat supplies', 1951.54, 'For feeding program', 1),
('2026-01-17', 'CHK-003-2026', 'ABC Hardware Supply', 'DV-2026-003', 5000.00, 'General Fund', '', '267.85', 'Construction materials', 4732.15, 'Barangay hall repair', 1),
('2026-01-18', 'CHK-004-2026', 'Maria Santos', 'DV-2026-004', 8000.00, 'General Fund', 'Salary - January', '180.00', 'Salary payment for Barangay Secretary', 7820.00, 'Monthly salary', 1),
('2026-01-19', 'CHK-005-2026', 'PLDT Home', 'DV-2026-005', 1500.00, 'General Fund', '', '', 'Internet and telephone bills', 1500.00, 'Monthly utility', 1);

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

