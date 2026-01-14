-- Barangay Treasurer Management System Database Schema
-- Barangay Sto. Rosario, Magallanes, Agusan del Norte

-- 1. Create the database
CREATE DATABASE IF NOT EXISTS treasurer_management;

-- 2. Tell MySQL to use this database
USE treasurer_management;

-- 3. Create Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    role VARCHAR(50),
    username VARCHAR(50) UNIQUE, -- Unique prevents duplicate accounts
    password VARCHAR(255)
);

-- 4. Create Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payer_name VARCHAR(150),
    service_type VARCHAR(100),     -- brgy clearance, cedula
    purpose VARCHAR(255),
    amount DECIMAL(10,2),
    bir_tax DECIMAL(10,2),
    receipt_no VARCHAR(50),
    paid_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    payment_date DATE,
    remarks TEXT,
    received_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (received_by) REFERENCES users(id)
);

-- 5. Create Cedula Table
CREATE TABLE IF NOT EXISTS cedula (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula_no VARCHAR(50),
    full_name VARCHAR(150),
    address VARCHAR(255),
    age INT,
    birth_date DATE,
    sex ENUM('Male', 'Female'),
    birth_place VARCHAR(150),
    civil_status VARCHAR(20),
    occupation VARCHAR(100),
    tin VARCHAR(50),
    height DECIMAL(5, 2),
    weight DECIMAL(5, 2),
    amount DECIMAL(10,2),
    issued_date DATE,
    remarks TEXT,
    issued_by INT,
    FOREIGN KEY (issued_by) REFERENCES users(id)
);

-- 6. Create Monthly Collections Table
CREATE TABLE IF NOT EXISTS monthly_collections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    month VARCHAR(20),
    year INT,
    category VARCHAR(100),        -- Tax Revenue / Non-Tax
    description VARCHAR(255),
    amount DECIMAL(12,2)
);

-- 7. Create BIR Records Table
CREATE TABLE IF NOT EXISTS bir_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tin VARCHAR(50),
    payee VARCHAR(150),
    record_date DATE,
    gross_amount DECIMAL(12,2),
    base_amount DECIMAL(12,2),
    one_percent DECIMAL(12,2),
    five_percent DECIMAL(12,2),
    total_amount DECIMAL(12,2),
    remarks TEXT,
    recorded_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- 8. Create Disbursements Table
CREATE TABLE IF NOT EXISTS disbursements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    disburse_date DATE,
    check_no VARCHAR(50),
    payee VARCHAR(150),
    dv_no VARCHAR(50),
    amount DECIMAL(12,2),
    fund VARCHAR(100),
    payroll VARCHAR(100),
    bir VARCHAR(100),
    purpose VARCHAR(255),
    release_amount DECIMAL(12,2),
    remarks TEXT,
    processed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (processed_by) REFERENCES users(id)
);

-- 9. Create Cashbook Table
CREATE TABLE IF NOT EXISTS cashbook (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trans_date DATE,
    particulars VARCHAR(255),
    debit DECIMAL(12,2),
    credit DECIMAL(12,2),
    balance DECIMAL(12,2)
);

-- Insert default treasurer user
-- Default password: treasurer123 (remember to change this!)
-- Password hash generated using: password_hash('treasurer123', PASSWORD_DEFAULT)
INSERT INTO users (username, password, name, role) VALUES
('treasurer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Barangay Treasurer', 'treasurer');

-- Sample data for testing (optional)
-- Uncomment to add sample data
/*
INSERT INTO payments (payer_name, service_type, purpose, amount, bir_tax, receipt_no, received_by) VALUES
('Juan Dela Cruz', 'Barangay Clearance', 'Employment', 100.00, 5.00, '100001', 1),
('Maria Santos', 'Certificate of Indigency', 'Medical Assistance', 50.00, 0.00, '100002', 1);

INSERT INTO cedula (full_name, address, age, birth_place, occupation, tin, amount, issued_date, issued_by) VALUES
('Pedro Reyes', 'Purok 1, Sto. Rosario', 35, 'Magallanes', 'Farmer', '123-456-789', 50.00, CURDATE(), 1);
*/

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('treasurer', 'admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Clients Table
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    address TEXT,
    contact_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_no VARCHAR(50) NOT NULL,
    payment_date DATE NOT NULL,
    payer_name VARCHAR(150) NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    purpose VARCHAR(255) NOT NULL,
    purpose_details TEXT,
    amount DECIMAL(10, 2) NOT NULL,
    bir_tax DECIMAL(10, 2) DEFAULT 0,
    remarks TEXT,
    received_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (received_by) REFERENCES users(id)
);

-- Cedula Table
CREATE TABLE IF NOT EXISTS cedula (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula_no VARCHAR(50) UNIQUE NOT NULL,
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
    remarks TEXT,
    issued_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (issued_by) REFERENCES users(id)
);

-- BIR Records Table
CREATE TABLE IF NOT EXISTS bir_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tin VARCHAR(50) NOT NULL,
    payee VARCHAR(150) NOT NULL,
    record_date DATE NOT NULL,
    gross_amount DECIMAL(12, 2) NOT NULL,
    base_amount DECIMAL(12, 2) NOT NULL,
    one_percent DECIMAL(10, 2) NOT NULL,
    five_percent DECIMAL(10, 2) NOT NULL,
    total_amount DECIMAL(12, 2) GENERATED ALWAYS AS (gross_amount) STORED,
    remarks TEXT,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- Disbursements Table
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
    FOREIGN KEY (processed_by) REFERENCES users(id)
);

-- Insert default treasurer user
-- Default password: treasurer123 (remember to change this!)
INSERT INTO users (username, password, name, role) VALUES
('treasurer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Barangay Treasurer', 'treasurer');

-- Sample data for testing (optional)
INSERT INTO clients (full_name, address, contact_number) VALUES
('Juan Dela Cruz', 'Purok 1, Sto. Rosario, Magallanes', '09123456789'),
('Maria Santos', 'Purok 2, Sto. Rosario, Magallanes', '09187654321');
