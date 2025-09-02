-- ============================================================================
-- OSBUZZ Database - Clear All Test Data
-- This script will remove all test data while preserving table structure
-- ============================================================================

-- Disable foreign key checks to avoid constraint issues during deletion
SET FOREIGN_KEY_CHECKS = 0;

-- Clear cart data
DELETE FROM cart;

-- Clear order related data (in correct order due to foreign keys)
DELETE FROM order_status_history;
DELETE FROM order_items;
DELETE FROM orders;

-- Clear customer addresses
DELETE FROM customer_addresses;

-- Clear password reset tokens
DELETE FROM password_resets;

-- Clear product related data (in correct order due to foreign keys)
DELETE FROM product_photos;
DELETE FROM product_variants;
DELETE FROM product;

-- Clear categories
DELETE FROM category;

-- Clear users (keep this last or create a new admin user)
DELETE FROM user;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Reset auto-increment counters to start from 1
ALTER TABLE cart AUTO_INCREMENT = 1;
ALTER TABLE customer_addresses AUTO_INCREMENT = 1;
ALTER TABLE orders AUTO_INCREMENT = 1;
ALTER TABLE order_items AUTO_INCREMENT = 1;
ALTER TABLE order_status_history AUTO_INCREMENT = 1;
ALTER TABLE password_resets AUTO_INCREMENT = 1;
ALTER TABLE product AUTO_INCREMENT = 1;
ALTER TABLE product_photos AUTO_INCREMENT = 1;
ALTER TABLE product_variants AUTO_INCREMENT = 1;
ALTER TABLE category AUTO_INCREMENT = 1;
ALTER TABLE user AUTO_INCREMENT = 1;

-- Optional: Create a default admin user
-- Uncomment the following lines if you want to create a default admin account
-- Password is 'admin123' (SHA1 hashed)
INSERT INTO user (username, email, password, photo, role, created_at, status) VALUES 
('admin', 'admin@osbuzz.com', 'f865b53623b121fd34ee5426c792e5c33af8c227', '', 'Admin', NOW(), 'active');



-- Success message
SELECT 'All test data has been successfully cleared!' as Message;
