-- ============================================================
-- LAND VALUATION MANAGEMENT SYSTEM - DATABASE SCHEMA
-- Student: Asha Othman Khamis (KIST/ICT/24/0095)
-- Karume Institute of Science and Technology
-- ============================================================

-- Create database
CREATE DATABASE IF NOT EXISTS lvms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lvms_db;

-- Drop existing tables (for clean reinstall)
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS lands;
DROP TABLE IF EXISTS users;

-- ------------------------------------------------------------
-- USERS TABLE
-- ------------------------------------------------------------
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)        NOT NULL,
    email       VARCHAR(100) UNIQUE NOT NULL,
    password    VARCHAR(255)        NOT NULL,         -- store hashed password (password_hash())
    phone       VARCHAR(20)         NOT NULL,
    role        ENUM('seller','buyer','authority','admin') NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- LANDS TABLE
-- ------------------------------------------------------------
CREATE TABLE lands (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    seller_id           INT NOT NULL,
    title               VARCHAR(200) NOT NULL,
    plot_number         VARCHAR(50)  NOT NULL,
    location            VARCHAR(200) NOT NULL,
    zone                ENUM('urban','suburban','rural')               NOT NULL,
    type                ENUM('residential','commercial','agricultural') NOT NULL,
    area                DECIMAL(10,2) NOT NULL,        -- square meters
    road_access         ENUM('main','side','interior') NOT NULL,
    distance_to_center  ENUM('near','moderate','far') NULL,   -- residential & commercial only
    soil_quality        ENUM('fertile','moderate','poor') NULL, -- agricultural only
    water_source        ENUM('river','well','none') NULL,       -- agricultural only
    description         TEXT,
    has_electricity     TINYINT(1) DEFAULT 0,
    has_water           TINYINT(1) DEFAULT 0,
    has_road            TINYINT(1) DEFAULT 0,
    has_sewage          TINYINT(1) DEFAULT 0,
    status              ENUM('pending','valued','sold','rejected') DEFAULT 'pending',
    valuation_amount    DECIMAL(15,2) NULL,
    valuation_breakdown TEXT NULL,                     -- JSON breakdown of formula
    valued_by           VARCHAR(100) NULL,
    valued_on           DATE NULL,
    buyer_id            INT NULL,
    payment_status      VARCHAR(20) NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id)  REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- PAYMENTS TABLE
-- ------------------------------------------------------------
CREATE TABLE payments (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    land_id            INT NOT NULL,
    buyer_id           INT NOT NULL,
    seller_id          INT NOT NULL,
    amount             DECIMAL(15,2) NOT NULL,
    method             ENUM('mpesa','tigopesa','airtelmoney','bank','card') NOT NULL,
    reference          VARCHAR(100) NOT NULL,         -- phone / account number / card last4
    transaction_id     VARCHAR(100) UNIQUE NOT NULL,  -- TXN-LVS-YYYYMMDD-XXXXXX
    confirmation_code  VARCHAR(50) NOT NULL,          -- M-Pesa style code
    status             VARCHAR(20) DEFAULT 'successful',
    payment_date       DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (land_id)   REFERENCES lands(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id)  REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA (default accounts and sample lands)
-- ============================================================
-- Demo accounts created with placeholder password.
-- IMPORTANT: After importing this file, visit http://localhost/lvms/setup.php
-- once to hash the demo passwords properly. All demo accounts will use "1234".
INSERT INTO users (name, email, password, phone, role) VALUES
('Asha Seller',    'seller@demo.com',    'NEEDS_HASH', '+255712345678', 'seller'),
('Juma Buyer',     'buyer@demo.com',     'NEEDS_HASH', '+255712345679', 'buyer'),
('Land Authority', 'authority@demo.com', 'NEEDS_HASH', '+255712345680', 'authority'),
('System Admin',   'admin@demo.com',     'NEEDS_HASH', '+255712345681', 'admin');

-- Sample lands
INSERT INTO lands (seller_id, title, plot_number, location, zone, type, area, road_access, distance_to_center, description, has_electricity, has_water, has_road, has_sewage, status, valuation_amount, valued_by, valued_on) VALUES
(1, 'Residential Plot — Stone Town',  'ZNZ/ST/0451',  'Stone Town, Zanzibar',  'urban', 'residential', 500,  'main', 'near',  'Prime residential plot close to main road, suitable for family home.', 1, 1, 1, 0, 'valued', 50400000, 'Land Authority', CURDATE()),
(1, 'Commercial Land — Michenzani',   'ZNZ/MCH/0712', 'Michenzani, Zanzibar',  'urban', 'commercial',  800, 'main', 'near',  'Excellent for business premises; heavy foot traffic area.',          1, 1, 1, 1, 'valued', 120960000, 'Land Authority', CURDATE());

INSERT INTO lands (seller_id, title, plot_number, location, zone, type, area, road_access, soil_quality, water_source, description, has_electricity, has_water, has_road, has_sewage, status) VALUES
(1, 'Farmland — Kizimkazi', 'ZNZ/KZK/0238', 'Kizimkazi, Zanzibar', 'rural', 'agricultural', 5000, 'side', 'fertile', 'river', 'Fertile land ideal for farming, adjacent to clean water source.', 0, 1, 1, 0, 'pending');

INSERT INTO lands (seller_id, title, plot_number, location, zone, type, area, road_access, distance_to_center, description, has_electricity, has_water, has_road, has_sewage, status) VALUES
(1, 'Residential Plot — Fumba', 'ZNZ/FMB/0195', 'Fumba, Zanzibar', 'suburban', 'residential', 750, 'side', 'moderate', 'Quiet neighbourhood with future development plans.', 1, 1, 1, 0, 'pending');

-- ============================================================
-- DONE
-- ============================================================
SELECT 'Database installed successfully!' AS message;
SELECT COUNT(*) AS total_users FROM users;
SELECT COUNT(*) AS total_lands FROM lands;
