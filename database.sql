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
-- DEFAULT USER
-- Username: treasurer | Password: treasurer123 (MD5 hash)
-- ============================================================================
INSERT INTO users (username, password, name, role) VALUES
('treasurer', '5f4dcc3b5aa765d61d8327deb882cf99', 'Barangay Treasurer', 'treasurer')
ON DUPLICATE KEY UPDATE username = username;
