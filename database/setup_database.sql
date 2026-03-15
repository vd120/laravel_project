-- ============================================
-- Nexus - MySQL Database Setup Script
-- Manual database creation script
-- ============================================

-- Step 1: Create the database
-- Run this as a MySQL root or admin user
CREATE DATABASE IF NOT EXISTS `nexus` 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

-- Step 2: Create a dedicated user for the application
-- Replace 'nexus_user' and 'your_secure_password' with your desired credentials
CREATE USER IF NOT EXISTS 'nexus_user'@'localhost' 
    IDENTIFIED BY 'your_secure_password';

-- Step 3: Grant privileges to the user
GRANT ALL PRIVILEGES ON `nexus`.* TO 'nexus_user'@'localhost';

-- Step 4: Apply the privilege changes
FLUSH PRIVILEGES;

-- Step 5: Verify the setup
SELECT 'Database created successfully!' AS Status;
SHOW DATABASES LIKE 'nexus';

-- ============================================
-- Usage Instructions:
-- ============================================
-- 
-- 1. Save this file as setup_database.sql
-- 
-- 2. Run with MySQL command line:
--    mysql -u root -p < setup_database.sql
-- 
-- 3. Or run each command manually:
--    mysql -u root -p
--    source setup_database.sql
-- 
-- 4. Update your .env file:
--    DB_CONNECTION=mysql
--    DB_HOST=127.0.0.1
--    DB_PORT=3306
--    DB_DATABASE=nexus
--    DB_USERNAME=nexus_user
--    DB_PASSWORD=your_secure_password
-- 
-- 5. Run migrations:
--    php artisan migrate
-- 
-- ============================================
-- Alternative: Create database with different name
-- ============================================
-- 
-- CREATE DATABASE IF NOT EXISTS `your_database_name` 
--     CHARACTER SET utf8mb4 
--     COLLATE utf8mb4_unicode_ci;
-- 
-- CREATE USER IF NOT EXISTS 'your_username'@'localhost' 
--     IDENTIFIED BY 'your_password';
-- 
-- GRANT ALL PRIVILEGES ON `your_database_name`.* TO 'your_username'@'localhost';
-- 
-- FLUSH PRIVILEGES;
-- 
-- ============================================
-- For remote access (optional):
-- ============================================
-- 
-- CREATE USER IF NOT EXISTS 'nexus_user'@'%' 
--     IDENTIFIED BY 'your_secure_password';
-- 
-- GRANT ALL PRIVILEGES ON `nexus`.* TO 'nexus_user'@'%';
-- 
-- FLUSH PRIVILEGES;
-- 
-- ============================================
-- Security Notes:
-- ============================================
-- 1. Always use strong passwords in production
-- 2. Don't use 'root' for application connections
-- 3. Limit user privileges to only what's needed
-- 4. Use '%' only if remote access is required
-- 5. Consider using SSL for production connections
