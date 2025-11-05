-- =====================================================
-- Script to delete all records from #__ad_orders table
-- =====================================================
-- 
-- WARNING: This script will permanently delete ALL records!
-- Make sure to backup your database before running this script.
-- 
-- Usage:
-- 1. Replace #__ with your actual table prefix (e.g., xodhq_)
-- 2. Run this script in your database management tool (phpMyAdmin, MySQL Workbench, etc.)
-- 3. Or execute via command line: mysql -u username -p database_name < delete_ad_orders.sql
-- 
-- =====================================================

-- Step 1: Check current record count
SELECT COUNT(*) as 'Current Records' FROM `#__ad_orders`;

-- Step 2: Create backup table (optional but recommended)
-- Uncomment the following lines to create a backup:
-- CREATE TABLE `#__ad_orders_backup_2024` LIKE `#__ad_orders`;
-- INSERT INTO `#__ad_orders_backup_2024` SELECT * FROM `#__ad_orders`;

-- Step 3: Delete all records
-- WARNING: This will delete ALL records permanently!
DELETE FROM `#__ad_orders`;

-- Step 4: Reset AUTO_INCREMENT counter (optional)
-- This will reset the ID counter to start from 1 again
ALTER TABLE `#__ad_orders` AUTO_INCREMENT = 1;

-- Step 5: Verify deletion
SELECT COUNT(*) as 'Records After Deletion' FROM `#__ad_orders`;

-- =====================================================
-- Additional cleanup queries (run only if needed)
-- =====================================================

-- If you also want to clean up related tables, uncomment as needed:

-- Clean up payment info table (if it references ad_orders)
-- DELETE FROM `#__ad_payment_info` WHERE order_id IN (SELECT id FROM `#__ad_orders`);

-- Clean up user wallet table (if it references ad_orders)
-- DELETE FROM `#__ad_users` WHERE orderid IN (SELECT id FROM `#__ad_orders`);

-- =====================================================
-- Verification queries
-- =====================================================

-- Check table structure
DESCRIBE `#__ad_orders`;

-- Check table status
SHOW TABLE STATUS LIKE '#__ad_orders';

-- =====================================================
-- End of script
-- =====================================================
