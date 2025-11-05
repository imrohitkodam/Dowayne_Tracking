# Ad Orders Deletion Scripts

This package contains multiple scripts to safely delete all records from the `#__ad_orders` table in your SocialAds Joomla component.

## ⚠️ WARNING

**These scripts will permanently delete ALL records from the ad_orders table!**

- Always backup your database before running any deletion script
- Test on a development environment first
- Consider the impact on related tables and data integrity

## 📁 Files Included

1. **`delete_ad_orders.php`** - Web-based deletion interface
2. **`delete_ad_orders.sql`** - Direct SQL commands
3. **`cli/DeleteOrdersCommand.php`** - Joomla CLI command
4. **`delete_ad_orders.sh`** - Batch script for easy execution
5. **`README.md`** - This documentation file

## 🚀 Usage Options

### Option 1: Web Interface (Recommended for beginners)

```bash
# Make the script accessible via web browser
# Navigate to: http://your-domain.com/delete_ad_orders.php
```

**Features:**
- User-friendly web interface
- Table information display
- Optional backup creation
- Confirmation prompts
- Progress feedback

### Option 2: Batch Script (Recommended for advanced users)

```bash
# Make executable (if not already)
chmod +x delete_ad_orders.sh

# Show usage information
./delete_ad_orders.sh help

# Show table information only
./delete_ad_orders.sh info

# Create backup only (no deletion)
./delete_ad_orders.sh backup

# Run SQL deletion with prompts
./delete_ad_orders.sh sql

# Start web interface
./delete_ad_orders.sh web

# Run CLI command
./delete_ad_orders.sh cli
```

### Option 3: Direct SQL Execution

```sql
-- 1. Check current records
SELECT COUNT(*) FROM `xodhq_ad_orders`;

-- 2. Create backup (recommended)
CREATE TABLE `xodhq_ad_orders_backup_2024` LIKE `xodhq_ad_orders`;
INSERT INTO `xodhq_ad_orders_backup_2024` SELECT * FROM `xodhq_ad_orders`;

-- 3. Delete all records
DELETE FROM `xodhq_ad_orders`;

-- 4. Reset AUTO_INCREMENT (optional)
ALTER TABLE `xodhq_ad_orders` AUTO_INCREMENT = 1;

-- 5. Verify deletion
SELECT COUNT(*) FROM `xodhq_ad_orders`;
```

### Option 4: Joomla CLI Command

```bash
# Navigate to Joomla root directory
cd /var/www/ttpl-rt-234-php83.local/public/dowayne_last_testing

# Run CLI command
php cli/joomla.php socialads:delete-orders --help
php cli/joomla.php socialads:delete-orders --backup --reset-counter
php cli/joomla.php socialads:delete-orders --dry-run
```

## 🔧 Configuration

### Database Configuration

Update the database credentials in the scripts if needed:

```bash
# In delete_ad_orders.sh
DB_HOST="localhost"
DB_USER="root"
DB_PASS="root"
DB_NAME="dowayne_last_testing_db"
DB_PREFIX="xodhq_"
```

### Table Prefix

Replace `#__` with your actual table prefix (e.g., `xodhq_`) in SQL scripts.

## 📊 Table Structure

The `#__ad_orders` table contains:

| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) | Primary key |
| `prefix_oid` | varchar(23) | Order ID prefix |
| `cdate` | datetime | Order creation date |
| `mdate` | timestamp | Order modification date |
| `payment_info_id` | int(11) | Payment information ID |
| `transaction_id` | varchar(100) | Payment transaction ID |
| `payee_id` | varchar(100) | User who made payment |
| `amount` | float | Payment amount |
| `status` | varchar(100) | Payment status |
| `extras` | text | Additional payment details |
| `processor` | varchar(100) | Payment gateway |
| `ip_address` | varchar(100) | IP address |
| `comment` | varchar(255) | User comment |
| `original_amount` | float | Original amount |
| `coupon` | varchar(100) | Coupon ID |
| `tax` | float(10,2) | Tax amount |
| `tax_details` | text | Tax information |

## 🛡️ Safety Features

### Backup Creation
- Automatic backup table creation with timestamp
- Option to skip backup (not recommended)
- Backup verification

### Confirmation Prompts
- Multiple confirmation steps
- Clear warnings about data loss
- Dry-run mode for testing

### Error Handling
- Database connection validation
- Table existence checks
- Transaction rollback on errors

## 🔍 Verification

After running any deletion script, verify the results:

```sql
-- Check record count
SELECT COUNT(*) FROM `xodhq_ad_orders`;

-- Check table structure
DESCRIBE `xodhq_ad_orders`;

-- Check AUTO_INCREMENT value
SHOW TABLE STATUS LIKE 'xodhq_ad_orders';
```

## 🚨 Troubleshooting

### Common Issues

1. **Permission Denied**
   ```bash
   chmod +x delete_ad_orders.sh
   ```

2. **Database Connection Failed**
   - Check database credentials
   - Ensure MySQL service is running
   - Verify database name and prefix

3. **Table Not Found**
   - Check table prefix
   - Verify table name spelling
   - Ensure SocialAds component is installed

4. **PHP Errors**
   - Check PHP error logs
   - Ensure Joomla framework is loaded
   - Verify file permissions

### Recovery

If you need to restore data from backup:

```sql
-- Restore from backup table
INSERT INTO `xodhq_ad_orders` SELECT * FROM `xodhq_ad_orders_backup_2024`;

-- Or recreate table structure
CREATE TABLE `xodhq_ad_orders` LIKE `xodhq_ad_orders_backup_2024`;
INSERT INTO `xodhq_ad_orders` SELECT * FROM `xodhq_ad_orders_backup_2024`;
```

## 🧹 Cleanup

After successful deletion:

1. **Delete script files** (for security):
   ```bash
   rm delete_ad_orders.php
   rm delete_ad_orders.sql
   rm delete_ad_orders.sh
   rm -rf cli/DeleteOrdersCommand.php
   ```

2. **Remove backup tables** (when no longer needed):
   ```sql
   DROP TABLE `xodhq_ad_orders_backup_2024`;
   ```

## 📞 Support

If you encounter issues:

1. Check the error messages carefully
2. Verify database credentials and permissions
3. Test on a development environment first
4. Ensure you have proper backups before proceeding

## 📝 License

This script package is provided under the GNU General Public License version 2 or later, same as Joomla CMS.

---

**Remember: Always backup your data before running any deletion scripts!**

