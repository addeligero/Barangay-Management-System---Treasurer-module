# Barangay Sto. Rosario - Treasurer Management System

A comprehensive web-based treasurer management system designed for Barangay Sto. Rosario, Magallanes, Agusan del Norte.

## Features

### 📊 Dashboard

- Real-time statistics overview
- Monthly collection charts
- Recent transactions display
- Quick access to all modules

### 💰 Payment Records

- Record payments for various services (Barangay Clearance, Cedula, etc.)
- Track BIR fees and taxes
- Generate receipts
- Complete payment history

### 🆔 Cedula Management

- Issue Community Tax Certificates
- Store complete resident information
- Auto-generate cedula numbers
- Track all issued cedulas

### 📈 BIR Records

- Calculate 1% and 5% withholding tax
- Track TIN and payee information
- Automatic tax computation
- Comprehensive BIR reports

### 💸 Disbursement Records

- Record all disbursements
- Track check numbers and DV numbers
- Monitor fund allocation
- Manage payroll and BIR deductions

### 📑 Monthly Collections Report

- Itemized monthly collection statements
- Tax revenue tracking
- Internal Revenue Allotment
- Print-ready reports

## Design Theme

The system features the official Barangay Sto. Rosario color scheme:

- **Primary Yellow**: #FFD700 (Gold)
- **Primary Blue**: #1e3a5f (Navy Blue)
- Clean, modern, and responsive design
- Mobile-friendly interface

## Installation

1. Copy the project to your XAMPP htdocs folder
2. Create a database named `barangay_treasurer`
3. Import the database schema
4. Update database credentials in `config/database.php`
5. Access the system via `http://localhost/Treasurer_management_system`

## Database Tables

- `users` - System users and authentication
- `payments` - Payment records
- `cedula` - Cedula/Community Tax Certificates
- `bir_records` - BIR percentage records
- `disbursements` - Disbursement records
- `clients` - Client information (optional)

## Default Credentials

**Username**: treasurer
**Password**: (Set during installation)

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Web Server
- Modern web browser

## Security Features

- Session management
- Password hashing
- Role-based access control
- SQL injection prevention
- XSS protection

## Support

For support and inquiries:

- Barangay: Sto. Rosario
- Municipality: Magallanes
- Province: Agusan del Norte

---

© 2025 Barangay Sto. Rosario. All Rights Reserved.
