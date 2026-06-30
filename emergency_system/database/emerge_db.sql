CREATE DATABASE IF NOT EXISTS emerge_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE emerge_db;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS password_reset_tokens, emergency_requests, hospital, ambulance, police, users, admin;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE admin (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, avatar VARCHAR(10) DEFAULT '🛡️', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
INSERT INTO admin (name, email, password) VALUES ('Super Admin', 'admin@emergency.com', '$2y$10$fYREo6NZKKFgIXFGvC5sme05XvuiIkgzpiGeIEm6aGysDDUJJbzKa');

CREATE TABLE users (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL UNIQUE, phone VARCHAR(20) NOT NULL, password VARCHAR(255) NOT NULL, address TEXT, blood_group VARCHAR(5) DEFAULT NULL, emergency_contact VARCHAR(20) DEFAULT NULL, status ENUM('active','inactive') DEFAULT 'active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
INSERT INTO users (name, email, phone, password, address, blood_group, emergency_contact) VALUES 
('Rahul Sharma', 'rahul.s@example.com', '9876543210', '$2y$10$fYREo6NZKKFgIXFGvC5sme05XvuiIkgzpiGeIEm6aGysDDUJJbzKa', '12 MG Road, Bangalore', 'O+', '9876543211'),
('Priya Patel', 'priya.p@example.com', '9876543222', '$2y$10$fYREo6NZKKFgIXFGvC5sme05XvuiIkgzpiGeIEm6aGysDDUJJbzKa', '45 Brigade Road, Bangalore', 'B+', '9876543233');

CREATE TABLE police (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL UNIQUE, phone VARCHAR(20) NOT NULL, password VARCHAR(255) NOT NULL, badge_number VARCHAR(50) DEFAULT NULL, station_name VARCHAR(200) DEFAULT NULL, station_address TEXT, status ENUM('active','inactive') DEFAULT 'active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
INSERT INTO police (name, email, phone, password, badge_number, station_name, station_address) VALUES 
('Officer Ramesh Kumar', 'ramesh.police@emergency.com', '9123456781', '$2y$10$fYREo6NZKKFgIXFGvC5sme05XvuiIkgzpiGeIEm6aGysDDUJJbzKa', 'BLR-POL-001', 'Central Police Station', '1 MG Road, Bangalore'),
('Inspector Sunita Rao', 'sunita.police@emergency.com', '9123456782', '$2y$10$fYREo6NZKKFgIXFGvC5sme05XvuiIkgzpiGeIEm6aGysDDUJJbzKa', 'BLR-POL-002', 'Indiranagar Police Station', '100ft Road, Indiranagar, Bangalore');

CREATE TABLE ambulance (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL UNIQUE, phone VARCHAR(20) NOT NULL, password VARCHAR(255) NOT NULL, vehicle_number VARCHAR(50) DEFAULT NULL, driver_license VARCHAR(50) DEFAULT NULL, base_location TEXT, status ENUM('active','inactive') DEFAULT 'active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
INSERT INTO ambulance (name, email, phone, password, vehicle_number, driver_license, base_location) VALUES 
('Paramedic Suresh', 'suresh.amb@emergency.com', '9988776651', '$2y$10$fYREo6NZKKFgIXFGvC5sme05XvuiIkgzpiGeIEm6aGysDDUJJbzKa', 'KA-01-AB-1234', 'DL-KA-001', 'City Hospital Base, Bangalore'),
('Driver Amit Singh', 'amit.amb@emergency.com', '9988776652', '$2y$10$fYREo6NZKKFgIXFGvC5sme05XvuiIkgzpiGeIEm6aGysDDUJJbzKa', 'KA-03-XY-9876', 'DL-KA-002', 'Apollo Hospital Base, Bangalore');

CREATE TABLE hospital (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL UNIQUE, phone VARCHAR(20) NOT NULL, password VARCHAR(255) NOT NULL, hospital_name VARCHAR(200) DEFAULT NULL, hospital_address TEXT, specialization VARCHAR(200) DEFAULT NULL, bed_capacity INT DEFAULT NULL, status ENUM('active','inactive') DEFAULT 'active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
INSERT INTO hospital (name, email, phone, password, hospital_name, hospital_address, specialization, bed_capacity) VALUES 
('Dr. Vikram Rao', 'vikram.hosp@emergency.com', '9876500001', '$2y$10$fYREo6NZKKFgIXFGvC5sme05XvuiIkgzpiGeIEm6aGysDDUJJbzKa', 'City General Hospital', 'Station Road, Bangalore', 'Trauma & Emergency', 200),
('Dr. Anjali Desai', 'anjali.hosp@emergency.com', '9876500002', '$2y$10$fYREo6NZKKFgIXFGvC5sme05XvuiIkgzpiGeIEm6aGysDDUJJbzKa', 'Apollo Medical Center', 'Bannerghatta Road, Bangalore', 'Cardiac & Trauma', 350);

CREATE TABLE emergency_requests (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, user_name VARCHAR(100) NOT NULL, user_phone VARCHAR(20) DEFAULT NULL, service_type ENUM('Police','Ambulance','Hospital') NOT NULL, location_text VARCHAR(500) DEFAULT NULL, latitude DECIMAL(10,8) DEFAULT NULL, longitude DECIMAL(11,8) DEFAULT NULL, description TEXT, severity ENUM('Low','Medium','High','Critical') DEFAULT 'High', request_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP, status ENUM('Pending','Accepted','Resolved') DEFAULT 'Pending', accepted_by_id INT DEFAULT NULL, accepted_by_role VARCHAR(50) DEFAULT NULL, accepted_at TIMESTAMP NULL DEFAULT NULL, resolved_at TIMESTAMP NULL DEFAULT NULL, responder_note TEXT, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE);

CREATE TABLE password_reset_tokens (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, email VARCHAR(150) NOT NULL, token VARCHAR(64) NOT NULL UNIQUE, expires_at DATETIME NOT NULL, used TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE);
