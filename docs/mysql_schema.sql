CREATE DATABASE IF NOT EXISTS pharmacy_locator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pharmacy_locator;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS medicines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  brand_name VARCHAR(150) NOT NULL,
  generic_name VARCHAR(150) NOT NULL,
  uses_text TEXT NOT NULL,
  dosage TEXT NOT NULL,
  type VARCHAR(30) NOT NULL,
  location_code VARCHAR(80) NOT NULL,
  location_photo_url VARCHAR(255) NOT NULL DEFAULT '',
  package_photo_url VARCHAR(255) NOT NULL DEFAULT '',
  barcode VARCHAR(64) NOT NULL DEFAULT '',
  notes TEXT NOT NULL,
  requires_prescription TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_medicines_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_medicines_user_brand (user_id, brand_name),
  INDEX idx_medicines_user_generic (user_id, generic_name),
  INDEX idx_medicines_user_barcode (user_id, barcode),
  INDEX idx_medicines_user_location (user_id, location_code)
);

CREATE TABLE IF NOT EXISTS scan_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  barcode VARCHAR(64) NOT NULL,
  location_code VARCHAR(80) NOT NULL,
  scanned_at DATETIME NOT NULL,
  CONSTRAINT fk_scanlogs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_scan_logs_user_time (user_id, scanned_at)
);

-- Create your first local admin user.
-- Replace admin123! with a strong password.
-- Hash generator example (PHP CLI):
-- php -r "echo password_hash('admin123!', PASSWORD_DEFAULT), PHP_EOL;"

INSERT INTO users (username, password_hash)
VALUES ('admin', '$2y$10$REPLACE_WITH_HASH')
ON DUPLICATE KEY UPDATE username = username;
