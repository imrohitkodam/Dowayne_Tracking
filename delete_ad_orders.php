<?php
/**
 * Script to delete all records from #__ad_orders table
 * 
 * WARNING: This script will permanently delete ALL records from the ad_orders table!
 * Make sure to backup your database before running this script.
 * 
 * Usage:
 * 1. Via web browser: http://your-domain.com/delete_ad_orders.php
 * 2. Via command line: php delete_ad_orders.php
 * 
 * @package    SocialAds
 * @author     Assistant
 * @copyright  2024
 * @license    GNU General Public License version 2 or later
 */

// Security check - only allow execution in specific conditions
defined('_JEXEC') or define('_JEXEC', 1);

// Include Joomla framework
require_once __DIR__ . '/includes/app.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Ad Orders Deletion Script
 */
class AdOrdersDeleter
{
    private $db;
    private $tableName;
    private $backupTable = false;
    
    public function __construct()
    {
        $this->db = Factory::getDbo();
        $this->tableName = $this->db->getPrefix() . 'ad_orders';
    }
    
    /**
     * Get count of records in the table
     * 
     * @return int
     */
    public function getRecordCount()
    {
        $query = $this->db->getQuery(true)
            ->select('COUNT(*)')
            ->from($this->db->quoteName($this->tableName));
        
        $this->db->setQuery($query);
        return (int) $this->db->loadResult();
    }
    
    /**
     * Create backup table before deletion
     * 
     * @return bool
     */
    public function createBackup()
    {
        try {
            $backupTableName = $this->tableName . '_backup_' . date('Y_m_d_H_i_s');
            
            // Create backup table
            $backupQuery = "CREATE TABLE `{$backupTableName}` LIKE `{$this->tableName}`";
            $this->db->setQuery($backupQuery);
            $this->db->execute();
            
            // Copy data to backup table
            $copyQuery = "INSERT INTO `{$backupTableName}` SELECT * FROM `{$this->tableName}`";
            $this->db->setQuery($copyQuery);
            $this->db->execute();
            
            $this->backupTable = $backupTableName;
            return true;
        } catch (Exception $e) {
            echo "Error creating backup: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Delete all records from the table
     * 
     * @return bool
     */
    public function deleteAllRecords()
    {
        try {
            $query = $this->db->getQuery(true)
                ->delete($this->db->quoteName($this->tableName));
            
            $this->db->setQuery($query);
            $result = $this->db->execute();
            
            return $result;
        } catch (Exception $e) {
            echo "Error deleting records: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Reset AUTO_INCREMENT counter
     * 
     * @return bool
     */
    public function resetAutoIncrement()
    {
        try {
            $query = "ALTER TABLE `{$this->tableName}` AUTO_INCREMENT = 1";
            $this->db->setQuery($query);
            $this->db->execute();
            return true;
        } catch (Exception $e) {
            echo "Error resetting AUTO_INCREMENT: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Get table information
     * 
     * @return array
     */
    public function getTableInfo()
    {
        $query = "SHOW TABLE STATUS LIKE " . $this->db->quote($this->tableName);
        $this->db->setQuery($query);
        return $this->db->loadObject();
    }
    
    /**
     * Run the deletion process
     * 
     * @param bool $createBackup Whether to create backup before deletion
     * @param bool $resetAutoIncrement Whether to reset AUTO_INCREMENT
     * @return array
     */
    public function run($createBackup = true, $resetAutoIncrement = true)
    {
        $result = [
            'success' => false,
            'records_before' => 0,
            'records_after' => 0,
            'backup_created' => false,
            'backup_table' => null,
            'auto_increment_reset' => false,
            'message' => ''
        ];
        
        try {
            // Get initial record count
            $result['records_before'] = $this->getRecordCount();
            
            if ($result['records_before'] == 0) {
                $result['message'] = "No records found in {$this->tableName} table.";
                return $result;
            }
            
            echo "Found {$result['records_before']} records in {$this->tableName} table.\n";
            
            // Create backup if requested
            if ($createBackup) {
                echo "Creating backup table...\n";
                if ($this->createBackup()) {
                    $result['backup_created'] = true;
                    $result['backup_table'] = $this->backupTable;
                    echo "Backup created successfully: {$this->backupTable}\n";
                } else {
                    echo "Failed to create backup. Aborting deletion.\n";
                    $result['message'] = "Failed to create backup. Deletion aborted.";
                    return $result;
                }
            }
            
            // Delete all records
            echo "Deleting all records...\n";
            if ($this->deleteAllRecords()) {
                $result['records_after'] = $this->getRecordCount();
                echo "Successfully deleted all records.\n";
                
                // Reset AUTO_INCREMENT if requested
                if ($resetAutoIncrement) {
                    echo "Resetting AUTO_INCREMENT...\n";
                    if ($this->resetAutoIncrement()) {
                        $result['auto_increment_reset'] = true;
                        echo "AUTO_INCREMENT reset successfully.\n";
                    }
                }
                
                $result['success'] = true;
                $result['message'] = "Successfully deleted {$result['records_before']} records from {$this->tableName} table.";
                
                if ($createBackup) {
                    $result['message'] .= " Backup created: {$this->backupTable}";
                }
            } else {
                $result['message'] = "Failed to delete records.";
            }
            
        } catch (Exception $e) {
            $result['message'] = "Error: " . $e->getMessage();
            echo "Error: " . $e->getMessage() . "\n";
        }
        
        return $result;
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    // Command line execution
    echo "=== Ad Orders Deletion Script ===\n";
    echo "WARNING: This will delete ALL records from the ad_orders table!\n";
    echo "Make sure you have a database backup before proceeding.\n\n";
    
    $deleter = new AdOrdersDeleter();
    
    // Check if table exists
    $tableInfo = $deleter->getTableInfo();
    if (!$tableInfo) {
        echo "Error: Table {$deleter->tableName} not found!\n";
        exit(1);
    }
    
    echo "Table: {$deleter->tableName}\n";
    echo "Engine: {$tableInfo->Engine}\n";
    echo "Rows: {$tableInfo->Rows}\n\n";
    
    // Ask for confirmation
    echo "Do you want to proceed with deletion? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $confirmation = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($confirmation) !== 'yes') {
        echo "Deletion cancelled.\n";
        exit(0);
    }
    
    // Run deletion
    $result = $deleter->run(true, true);
    
    echo "\n=== Results ===\n";
    echo "Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
    echo "Records before: {$result['records_before']}\n";
    echo "Records after: {$result['records_after']}\n";
    echo "Backup created: " . ($result['backup_created'] ? 'Yes' : 'No') . "\n";
    if ($result['backup_table']) {
        echo "Backup table: {$result['backup_table']}\n";
    }
    echo "AUTO_INCREMENT reset: " . ($result['auto_increment_reset'] ? 'Yes' : 'No') . "\n";
    echo "Message: {$result['message']}\n";
    
} else {
    // Web execution
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Delete Ad Orders</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .warning { background: #ffebee; border: 1px solid #f44336; padding: 15px; margin: 20px 0; }
            .success { background: #e8f5e8; border: 1px solid #4caf50; padding: 15px; margin: 20px 0; }
            .error { background: #ffebee; border: 1px solid #f44336; padding: 15px; margin: 20px 0; }
            .info { background: #e3f2fd; border: 1px solid #2196f3; padding: 15px; margin: 20px 0; }
            button { background: #f44336; color: white; padding: 10px 20px; border: none; cursor: pointer; }
            button:hover { background: #d32f2f; }
            .backup-btn { background: #2196f3; }
            .backup-btn:hover { background: #1976d2; }
        </style>
    </head>
    <body>
        <h1>Delete Ad Orders Script</h1>
        
        <div class="warning">
            <strong>⚠️ WARNING:</strong> This script will permanently delete ALL records from the ad_orders table!
            Make sure you have a database backup before proceeding.
        </div>
        
        <?php
        if (isset($_POST['action'])) {
            $deleter = new AdOrdersDeleter();
            
            // Check if table exists
            $tableInfo = $deleter->getTableInfo();
            if (!$tableInfo) {
                echo '<div class="error">Error: Table not found!</div>';
            } else {
                if ($_POST['action'] === 'info') {
                    // Show table information
                    $recordCount = $deleter->getRecordCount();
                    echo '<div class="info">';
                    echo '<h3>Table Information</h3>';
                    echo '<p><strong>Table:</strong> ' . $deleter->tableName . '</p>';
                    echo '<p><strong>Engine:</strong> ' . $tableInfo->Engine . '</p>';
                    echo '<p><strong>Rows:</strong> ' . $tableInfo->Rows . '</p>';
                    echo '<p><strong>Current Records:</strong> ' . $recordCount . '</p>';
                    echo '</div>';
                    
                    if ($recordCount > 0) {
                        echo '<form method="post">';
                        echo '<input type="hidden" name="action" value="delete">';
                        echo '<p><label><input type="checkbox" name="create_backup" checked> Create backup before deletion</label></p>';
                        echo '<p><label><input type="checkbox" name="reset_auto_increment" checked> Reset AUTO_INCREMENT counter</label></p>';
                        echo '<button type="submit" onclick="return confirm(\'Are you sure you want to delete ALL records?\')">Delete All Records</button>';
                        echo '</form>';
                    } else {
                        echo '<div class="info">No records found in the table.</div>';
                    }
                    
                } elseif ($_POST['action'] === 'delete') {
                    // Perform deletion
                    $createBackup = isset($_POST['create_backup']);
                    $resetAutoIncrement = isset($_POST['reset_auto_increment']);
                    
                    $result = $deleter->run($createBackup, $resetAutoIncrement);
                    
                    if ($result['success']) {
                        echo '<div class="success">';
                        echo '<h3>✅ Deletion Successful!</h3>';
                        echo '<p>' . $result['message'] . '</p>';
                        echo '</div>';
                    } else {
                        echo '<div class="error">';
                        echo '<h3>❌ Deletion Failed!</h3>';
                        echo '<p>' . $result['message'] . '</p>';
                        echo '</div>';
                    }
                }
            }
        } else {
            // Show initial form
            echo '<form method="post">';
            echo '<input type="hidden" name="action" value="info">';
            echo '<button type="submit">Show Table Information</button>';
            echo '</form>';
        }
        ?>
        
        <div class="info">
            <h3>Usage Instructions:</h3>
            <ol>
                <li>Click "Show Table Information" to see current table status</li>
                <li>If records exist, you'll see deletion options</li>
                <li>Choose whether to create a backup (recommended)</li>
                <li>Click "Delete All Records" to proceed</li>
                <li>Confirm the deletion when prompted</li>
            </ol>
        </div>
        
        <div class="warning">
            <strong>Security Note:</strong> Delete this script file after use for security reasons.
        </div>
    </body>
    </html>
    <?php
}
?>

