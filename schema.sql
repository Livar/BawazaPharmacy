CREATE TABLE pharmacies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(id),
    UNIQUE KEY uniq_pharmacy_username (pharmacy_id, username)
);

CREATE TABLE taxi_drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    balance_iqd DECIMAL(12,2) NOT NULL DEFAULT 0,
    balance_usd DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(id)
);

CREATE TABLE deliveries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT NOT NULL,
    taxi_driver_id INT NULL,
    receipt_barcode VARCHAR(100) NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(50),
    customer_address VARCHAR(255),
    taxi_driver_name VARCHAR(150) NOT NULL,
    taxi_driver_phone VARCHAR(50),
    delivery_fee_amount DECIMAL(10,2) NOT NULL,
    delivery_fee_currency ENUM('IQD', 'USD') NOT NULL,
    payment_method ENUM('cash', 'fib', 'qi', 'other') NOT NULL,
    customer_payment_status ENUM('paid', 'unpaid', 'taxi_collects') NOT NULL,
    amount_collected_by_taxi DECIMAL(10,2) NOT NULL DEFAULT 0,
    amount_collected_currency ENUM('IQD', 'USD') NOT NULL DEFAULT 'IQD',
    status ENUM('created', 'given_to_taxi', 'delivered', 'money_collected', 'settled') NOT NULL DEFAULT 'created',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(id),
    FOREIGN KEY (taxi_driver_id) REFERENCES taxi_drivers(id)
);

CREATE TABLE delivery_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    delivery_id INT NOT NULL,
    event VARCHAR(50) NOT NULL,
    note TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (delivery_id) REFERENCES deliveries(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE cash_counts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT NOT NULL,
    count_date DATE NOT NULL,
    notes VARCHAR(255),
    created_by INT NOT NULL,
    updated_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(id)
);

CREATE TABLE cash_count_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cash_count_id INT NOT NULL,
    currency ENUM('IQD', 'USD') NOT NULL,
    denomination DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    FOREIGN KEY (cash_count_id) REFERENCES cash_counts(id)
);

CREATE TABLE staff_shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    clock_in DATETIME NOT NULL,
    clock_out DATETIME NULL,
    created_by INT NOT NULL,
    updated_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    UNIQUE KEY uniq_setting (pharmacy_id, setting_key),
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(id)
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT NOT NULL,
    user_id INT NULL,
    message VARCHAR(255) NOT NULL,
    link VARCHAR(255) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO pharmacies (name, status) VALUES ('Main Pharmacy', 'active');

INSERT INTO settings (pharmacy_id, setting_key, setting_value) VALUES
(1, 'exchange_rate', '1500'),
(1, 'app_name', 'Pharmacy Manager');

INSERT INTO taxi_drivers (pharmacy_id, name, phone, status, balance_iqd, balance_usd) VALUES
(1, 'Default Driver', '0000000000', 'active', 0, 0);

-- Sample admin user (password: admin123)
INSERT INTO users (pharmacy_id, name, username, password_hash, role, status) VALUES
(1, 'Admin User', 'admin', '$2y$10$Q2iBzwFY8r5D6iYB8fXU8eG12f0XKXT9j/6c0RrIvV7WcK9iQbC12', 'admin', 'active');
