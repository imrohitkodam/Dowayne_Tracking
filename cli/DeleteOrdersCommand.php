<?php
/**
 * Joomla CLI Command to delete all records from #__ad_orders table
 * 
 * This is a safer version that runs within the Joomla framework
 * 
 * Usage: php cli/joomla.php socialads:delete-orders [options]
 * 
 * @package    SocialAds
 * @author     Assistant
 * @copyright  2024
 * @license    GNU General Public License version 2 or later
 */

namespace Joomla\Component\Socialads\Cli\Command;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI command to delete ad orders
 */
class DeleteOrdersCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'socialads:delete-orders';

    /**
     * Configure the command
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function configure(): void
    {
        $this->setDescription('Delete all records from the ad_orders table');
        $this->setHelp('This command will permanently delete all records from the ad_orders table. Use with caution!');
        
        $this->addOption(
            'backup',
            'b',
            InputOption::VALUE_NONE,
            'Create backup table before deletion'
        );
        
        $this->addOption(
            'reset-counter',
            'r',
            InputOption::VALUE_NONE,
            'Reset AUTO_INCREMENT counter after deletion'
        );
        
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Skip confirmation prompt'
        );
        
        $this->addOption(
            'dry-run',
            'd',
            InputOption::VALUE_NONE,
            'Show what would be deleted without actually deleting'
        );
    }

    /**
     * Execute the command
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @since   4.0.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('SocialAds: Delete Ad Orders');
        
        // Get database connection
        $db = Factory::getDbo();
        $tableName = $db->getPrefix() . 'ad_orders';
        
        // Check if table exists
        $query = "SHOW TABLES LIKE " . $db->quote($tableName);
        $db->setQuery($query);
        if (!$db->loadResult()) {
            $io->error("Table {$tableName} not found!");
            return 1;
        }
        
        // Get current record count
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($tableName));
        $db->setQuery($query);
        $recordCount = (int) $db->loadResult();
        
        $io->section('Table Information');
        $io->table(
            ['Property', 'Value'],
            [
                ['Table Name', $tableName],
                ['Current Records', $recordCount],
                ['Backup Option', $input->getOption('backup') ? 'Yes' : 'No'],
                ['Reset Counter', $input->getOption('reset-counter') ? 'Yes' : 'No'],
                ['Dry Run', $input->getOption('dry-run') ? 'Yes' : 'No']
            ]
        );
        
        if ($recordCount === 0) {
            $io->success('No records found in the table. Nothing to delete.');
            return 0;
        }
        
        // Dry run mode
        if ($input->getOption('dry-run')) {
            $io->note("DRY RUN MODE: Would delete {$recordCount} records from {$tableName}");
            if ($input->getOption('backup')) {
                $io->note("Would create backup table: {$tableName}_backup_" . date('Y_m_d_H_i_s'));
            }
            if ($input->getOption('reset-counter')) {
                $io->note('Would reset AUTO_INCREMENT counter to 1');
            }
            return 0;
        }
        
        // Confirmation
        if (!$input->getOption('force')) {
            $io->warning("This will permanently delete {$recordCount} records from {$tableName}!");
            
            if (!$io->confirm('Are you sure you want to proceed?', false)) {
                $io->info('Operation cancelled.');
                return 0;
            }
        }
        
        try {
            // Create backup if requested
            if ($input->getOption('backup')) {
                $io->section('Creating Backup');
                $backupTableName = $tableName . '_backup_' . date('Y_m_d_H_i_s');
                
                // Create backup table
                $backupQuery = "CREATE TABLE `{$backupTableName}` LIKE `{$tableName}`";
                $db->setQuery($backupQuery);
                $db->execute();
                
                // Copy data to backup table
                $copyQuery = "INSERT INTO `{$backupTableName}` SELECT * FROM `{$tableName}`";
                $db->setQuery($copyQuery);
                $db->execute();
                
                $io->success("Backup created: {$backupTableName}");
            }
            
            // Delete all records
            $io->section('Deleting Records');
            $query = $db->getQuery(true)
                ->delete($db->quoteName($tableName));
            $db->setQuery($query);
            $db->execute();
            
            $io->success("Successfully deleted {$recordCount} records");
            
            // Reset AUTO_INCREMENT if requested
            if ($input->getOption('reset-counter')) {
                $io->section('Resetting Counter');
                $resetQuery = "ALTER TABLE `{$tableName}` AUTO_INCREMENT = 1";
                $db->setQuery($resetQuery);
                $db->execute();
                $io->success('AUTO_INCREMENT counter reset to 1');
            }
            
            // Verify deletion
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName($tableName));
            $db->setQuery($query);
            $remainingCount = (int) $db->loadResult();
            
            $io->section('Verification');
            $io->table(
                ['Property', 'Value'],
                [
                    ['Records Before', $recordCount],
                    ['Records After', $remainingCount],
                    ['Records Deleted', $recordCount - $remainingCount]
                ]
            );
            
            $io->success('Operation completed successfully!');
            
        } catch (\Exception $e) {
            $io->error('Error: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}

