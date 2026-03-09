-- =====================================================
-- Elderly Care Residence Management & Emergency Support Platform
-- Database Schema
-- =====================================================

-- Create database
CREATE DATABASE IF NOT EXISTS elderly_care_db;
USE elderly_care_db;

-- =====================================================
-- Table: users (Authentication)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('elderly', 'admin', 'family') NOT NULL,
    resident_id INT DEFAULT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_role (role),
    INDEX idx_resident_id (resident_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: residents (Resident Information)
-- =====================================================
CREATE TABLE IF NOT EXISTS residents (
    resident_id INT PRIMARY KEY AUTO_INCREMENT,
    name_en VARCHAR(100) NOT NULL COMMENT 'Name in English letters (Bangla name)',
    name_bn VARCHAR(100) COMMENT 'Name in Bangla',
    age INT NOT NULL,
    photo VARCHAR(255) DEFAULT 'default_photo.jpg',
    room_number VARCHAR(20) UNIQUE NOT NULL,
    phone VARCHAR(20),
    emergency_contact VARCHAR(20),
    emergency_contact_name VARCHAR(100),
    admission_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_room (room_number),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: medical_info (Medical Information)
-- =====================================================
CREATE TABLE IF NOT EXISTS medical_info (
    medical_id INT PRIMARY KEY AUTO_INCREMENT,
    resident_id INT NOT NULL,
    has_diabetes BOOLEAN DEFAULT FALSE,
    has_blood_pressure BOOLEAN DEFAULT FALSE,
    has_heart_condition BOOLEAN DEFAULT FALSE,
    allergies TEXT,
    special_notes TEXT,
    diet_type ENUM('normal', 'diabetes-friendly', 'low-salt', 'soft-food', 'heart-patient') DEFAULT 'normal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id) ON DELETE CASCADE,
    INDEX idx_resident (resident_id),
    INDEX idx_diet (diet_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: health_records (Daily Health Records)
-- =====================================================
CREATE TABLE IF NOT EXISTS health_records (
    record_id INT PRIMARY KEY AUTO_INCREMENT,
    resident_id INT NOT NULL,
    sugar_level DECIMAL(5,2) COMMENT 'mg/dL',
    blood_pressure_systolic INT COMMENT 'mmHg',
    blood_pressure_diastolic INT COMMENT 'mmHg',
    overall_condition TEXT,
    sleep_time_hours DECIMAL(4,2),
    medicine_taken BOOLEAN DEFAULT FALSE,
    medicine_details TEXT,
    recorded_date DATE NOT NULL,
    recorded_time TIME NOT NULL,
    recorded_by ENUM('resident', 'staff', 'admin') DEFAULT 'resident',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id) ON DELETE CASCADE,
    INDEX idx_resident (resident_id),
    INDEX idx_date (recorded_date),
    INDEX idx_resident_date (resident_id, recorded_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: meal_choices (Meal Selections)
-- =====================================================
CREATE TABLE IF NOT EXISTS meal_choices (
    meal_id INT PRIMARY KEY AUTO_INCREMENT,
    resident_id INT NOT NULL,
    meal_type ENUM('breakfast', 'lunch', 'snacks', 'dinner') NOT NULL,
    meal_name VARCHAR(200) NOT NULL,
    meal_description TEXT,
    meal_date DATE NOT NULL,
    selected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id) ON DELETE CASCADE,
    INDEX idx_resident (resident_id),
    INDEX idx_date (meal_date),
    INDEX idx_type_date (meal_type, meal_date),
    INDEX idx_resident_date (resident_id, meal_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: meal_plans (Kitchen Meal Plans)
-- =====================================================
CREATE TABLE IF NOT EXISTS meal_plans (
    plan_id INT PRIMARY KEY AUTO_INCREMENT,
    meal_type ENUM('breakfast', 'lunch', 'snacks', 'dinner') NOT NULL,
    meal_name VARCHAR(200) NOT NULL,
    meal_description TEXT,
    diet_type ENUM('normal', 'diabetes-friendly', 'low-salt', 'soft-food', 'heart-patient') NOT NULL,
    calories INT,
    price DECIMAL(10,2) DEFAULT 0.00,
    is_available BOOLEAN DEFAULT TRUE,
    plan_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (meal_type),
    INDEX idx_diet (diet_type),
    INDEX idx_date (plan_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: emergency_logs (Emergency Incidents)
-- =====================================================
CREATE TABLE IF NOT EXISTS emergency_logs (
    emergency_id INT PRIMARY KEY AUTO_INCREMENT,
    resident_id INT NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    emergency_type ENUM('medical', 'fall', 'other', 'unknown') DEFAULT 'unknown',
    description TEXT,
    emergency_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    responded_by INT NULL COMMENT 'Staff/Admin user_id',
    response_time TIMESTAMP NULL,
    status ENUM('pending', 'in-progress', 'resolved', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id) ON DELETE CASCADE,
    INDEX idx_resident (resident_id),
    INDEX idx_status (status),
    INDEX idx_time (emergency_time),
    INDEX idx_pending (status, emergency_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: payments (Financial Transactions)
-- =====================================================
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    resident_id INT NOT NULL,
    transaction_type ENUM('deposit', 'withdrawal', 'service_charge', 'premium_payment') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'bkash', 'rocket', 'bank_transfer') NOT NULL,
    transaction_date DATE NOT NULL,
    transaction_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    reference_number VARCHAR(100),
    processed_by INT NULL COMMENT 'Admin user_id',
    balance_after DECIMAL(10,2) COMMENT 'Balance after this transaction',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id) ON DELETE CASCADE,
    INDEX idx_resident (resident_id),
    INDEX idx_date (transaction_date),
    INDEX idx_type (transaction_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: account_balance (Resident Account Balance)
-- =====================================================
CREATE TABLE IF NOT EXISTS account_balance (
    account_id INT PRIMARY KEY AUTO_INCREMENT,
    resident_id INT NOT NULL UNIQUE,
    current_balance DECIMAL(10,2) DEFAULT 0.00,
    last_transaction_date DATE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id) ON DELETE CASCADE,
    INDEX idx_resident (resident_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: premium_services (Premium Package Subscriptions)
-- =====================================================
CREATE TABLE IF NOT EXISTS premium_services (
    premium_id INT PRIMARY KEY AUTO_INCREMENT,
    resident_id INT NOT NULL,
    package_name VARCHAR(100) DEFAULT 'Premium Package',
    package_price DECIMAL(10,2) DEFAULT 10000.00,
    start_date DATE NOT NULL,
    end_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    benefits TEXT COMMENT 'Extra checkups, Priority medical response, Private caregiver, Special meals',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id) ON DELETE CASCADE,
    INDEX idx_resident (resident_id),
    INDEX idx_active (is_active),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: ratings (Rating & Feedback)
-- =====================================================
CREATE TABLE IF NOT EXISTS ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    resident_id INT NOT NULL,
    rated_by ENUM('resident', 'family', 'staff') NOT NULL,
    category ENUM('food_service', 'cleanliness', 'medical_care', 'overall') NOT NULL,
    rating_value INT NOT NULL CHECK (rating_value >= 1 AND rating_value <= 5),
    feedback_text TEXT,
    rating_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id) ON DELETE CASCADE,
    INDEX idx_resident (resident_id),
    INDEX idx_category (category),
    INDEX idx_date (rating_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: medicine_schedule (Medicine Schedule)
-- =====================================================
CREATE TABLE IF NOT EXISTS medicine_schedule (
    schedule_id INT PRIMARY KEY AUTO_INCREMENT,
    resident_id INT NOT NULL,
    medicine_name VARCHAR(200) NOT NULL,
    dosage VARCHAR(100),
    frequency VARCHAR(100) COMMENT 'e.g., "3 times a day", "Morning and Evening"',
    schedule_time TIME,
    start_date DATE NOT NULL,
    end_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id) ON DELETE CASCADE,
    INDEX idx_resident (resident_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Insert Sample Data (for testing)
-- =====================================================

-- Insert sample admin user (password: admin123 - hashed)
INSERT INTO users (username, password, role, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin@elderlycare.com');

-- Insert sample resident
INSERT INTO residents (name_en, name_bn, age, room_number, phone, emergency_contact, emergency_contact_name, admission_date) VALUES
('Abdul Karim', 'আব্দুল করিম', 75, 'R-101', '01712345678', '01787654321', 'Rashida Begum', '2024-01-15'),
('Fatema Khatun', 'ফাতেমা খাতুন', 68, 'R-102', '01723456789', '01776543210', 'Hasan Ali', '2024-01-20'),
('Mohammad Ali', 'মোহাম্মদ আলী', 72, 'R-103', '01734567890', '01765432109', 'Ayesha Begum', '2024-02-01');

-- Insert sample elderly user (password: elderly123 - hashed)
INSERT INTO users (username, password, role, resident_id, email) VALUES
('elderly1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'elderly', 1, 'karim@example.com');

-- Insert medical info
INSERT INTO medical_info (resident_id, has_diabetes, has_blood_pressure, has_heart_condition, allergies, diet_type) VALUES
(1, TRUE, TRUE, FALSE, 'Peanuts, Dust', 'diabetes-friendly'),
(2, FALSE, TRUE, FALSE, 'None', 'low-salt'),
(3, TRUE, FALSE, TRUE, 'Shellfish', 'heart-patient');

-- Initialize account balances
INSERT INTO account_balance (resident_id, current_balance) VALUES
(1, 50000.00),
(2, 75000.00),
(3, 60000.00);

-- Insert sample meal plans for today
INSERT INTO meal_plans (meal_type, meal_name, meal_description, diet_type, calories, plan_date) VALUES
('breakfast', 'Paratha with Egg', 'Fresh paratha with scrambled egg', 'normal', 350, CURDATE()),
('breakfast', 'Oatmeal with Fruits', 'Oatmeal with fresh fruits', 'diabetes-friendly', 280, CURDATE()),
('breakfast', 'Rice with Dal', 'Soft rice with lentil dal', 'soft-food', 320, CURDATE()),
('lunch', 'Fish Curry with Rice', 'Bengali fish curry with steamed rice', 'normal', 550, CURDATE()),
('lunch', 'Grilled Chicken with Vegetables', 'Grilled chicken with steamed vegetables', 'diabetes-friendly', 450, CURDATE()),
('dinner', 'Chicken Khichuri', 'Soft khichuri with chicken', 'soft-food', 400, CURDATE());
