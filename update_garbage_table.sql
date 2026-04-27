USE treasurer_management;

CREATE TABLE IF NOT EXISTS garbage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    garbage_ref VARCHAR(50) UNIQUE,
    resident_id INT DEFAULT NULL,
    resident_name VARCHAR(150) NOT NULL,
    garbage_date DATE NOT NULL,
    purpose VARCHAR(255) NOT NULL,
    recipient_activities TEXT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_garbage_resident_id ON garbage (resident_id);
