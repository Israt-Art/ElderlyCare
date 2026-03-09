-- =====================================================
-- Database Migration: Update for New Requirements
-- =====================================================

USE elderly_care_db;

-- =====================================================
-- 1. Update health_records table
-- Change BP to single field, remove sleep/medicine fields
-- =====================================================

-- Add new blood_pressure field (single field)
ALTER TABLE health_records 
ADD COLUMN blood_pressure VARCHAR(20) COMMENT 'Blood Pressure (single field, e.g., "120/80")' AFTER sugar_level;

-- Migrate existing data (combine systolic and diastolic)
UPDATE health_records 
SET blood_pressure = CONCAT(blood_pressure_systolic, '/', blood_pressure_diastolic)
WHERE blood_pressure_systolic IS NOT NULL AND blood_pressure_diastolic IS NOT NULL;

-- Drop old BP columns
ALTER TABLE health_records DROP COLUMN blood_pressure_systolic;
ALTER TABLE health_records DROP COLUMN blood_pressure_diastolic;

-- Drop sleep and medicine fields
ALTER TABLE health_records DROP COLUMN sleep_time_hours;
ALTER TABLE health_records DROP COLUMN medicine_taken;
ALTER TABLE health_records DROP COLUMN medicine_details;

-- =====================================================
-- 2. Update meal_choices table
-- Change from breakfast/lunch/snacks/dinner to sugar/salt/spicy with Low/Normal/High
-- =====================================================

-- Add new columns for intake levels
ALTER TABLE meal_choices 
ADD COLUMN sugar_intake ENUM('Low', 'Normal', 'High') NULL AFTER meal_type,
ADD COLUMN salt_intake ENUM('Low', 'Normal', 'High') NULL AFTER sugar_intake,
ADD COLUMN spicy_intake ENUM('Low', 'Normal', 'High') NULL AFTER salt_intake;

-- Drop old meal_type and meal_name columns (we'll keep them for now but they won't be used)
-- Actually, let's keep the structure but change the meal_type enum
ALTER TABLE meal_choices 
MODIFY COLUMN meal_type ENUM('sugar', 'salt', 'spicy') NULL;

-- Clear old data (optional - comment out if you want to keep old data)
-- DELETE FROM meal_choices WHERE meal_type IN ('breakfast', 'lunch', 'snacks', 'dinner');

-- =====================================================
-- 3. Add guardian information to residents table
-- =====================================================

ALTER TABLE residents
ADD COLUMN address TEXT NULL AFTER age,
ADD COLUMN health_condition TEXT NULL AFTER address,
ADD COLUMN medicine_taken TEXT NULL AFTER health_condition,
ADD COLUMN guardian_name VARCHAR(100) NULL AFTER medicine_taken,
ADD COLUMN guardian_address TEXT NULL AFTER guardian_name,
ADD COLUMN guardian_phone VARCHAR(20) NULL AFTER guardian_address,
ADD COLUMN guardian_nid VARCHAR(50) NULL AFTER guardian_phone,
ADD COLUMN guardian_relationship VARCHAR(50) NULL AFTER guardian_nid,
ADD COLUMN package_choice ENUM('Normal', 'Premium') DEFAULT 'Normal' AFTER guardian_relationship;

-- =====================================================
-- 4. Update payments table to track package type
-- =====================================================

ALTER TABLE payments
ADD COLUMN package_type ENUM('Normal', 'Premium') NULL AFTER transaction_type;
