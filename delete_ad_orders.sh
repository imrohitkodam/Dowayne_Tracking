#!/bin/bash
# =====================================================
# Batch script to delete all records from ad_orders table
# =====================================================
# 
# This script provides multiple options for deleting ad_orders records
# 
# Usage: ./delete_ad_orders.sh [option]
# 
# Options:
#   web     - Run the web-based deletion script
#   sql     - Execute SQL commands directly
#   cli     - Run Joomla CLI command
#   backup  - Create backup only (no deletion)
# 
# =====================================================

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DB_HOST="localhost"
DB_USER="root"
DB_PASS="root"
DB_NAME="dowayne_last_testing_db"
DB_PREFIX="xodhq_"
TABLE_NAME="${DB_PREFIX}ad_orders"

echo -e "${BLUE}=====================================================${NC}"
echo -e "${BLUE}    Ad Orders Deletion Script${NC}"
echo -e "${BLUE}=====================================================${NC}"
echo ""

# Function to show usage
show_usage() {
    echo "Usage: $0 [option]"
    echo ""
    echo "Options:"
    echo "  web     - Run the web-based deletion script"
    echo "  sql     - Execute SQL commands directly"
    echo "  cli     - Run Joomla CLI command"
    echo "  backup  - Create backup only (no deletion)"
    echo "  info    - Show table information only"
    echo ""
    echo "Examples:"
    echo "  $0 web     # Open web interface"
    echo "  $0 sql     # Run SQL deletion"
    echo "  $0 backup  # Create backup only"
    echo ""
}

# Function to create backup
create_backup() {
    echo -e "${YELLOW}Creating backup of ${TABLE_NAME}...${NC}"
    
    BACKUP_TABLE="${TABLE_NAME}_backup_$(date +%Y_%m_%d_%H_%M_%S)"
    
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" << EOF
CREATE TABLE \`$BACKUP_TABLE\` LIKE \`$TABLE_NAME\`;
INSERT INTO \`$BACKUP_TABLE\` SELECT * FROM \`$TABLE_NAME\`;
SELECT COUNT(*) as 'Backup Records' FROM \`$BACKUP_TABLE\`;
EOF
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Backup created successfully: $BACKUP_TABLE${NC}"
    else
        echo -e "${RED}✗ Failed to create backup${NC}"
        exit 1
    fi
}

# Function to show table info
show_info() {
    echo -e "${BLUE}Table Information:${NC}"
    
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" << EOF
SELECT COUNT(*) as 'Current Records' FROM \`$TABLE_NAME\`;
DESCRIBE \`$TABLE_NAME\`;
EOF
}

# Function to run SQL deletion
run_sql_deletion() {
    echo -e "${RED}WARNING: This will delete ALL records from $TABLE_NAME!${NC}"
    echo -e "${YELLOW}Make sure you have a database backup before proceeding.${NC}"
    echo ""
    
    read -p "Do you want to create a backup first? (y/n): " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        create_backup
    fi
    
    echo ""
    read -p "Are you sure you want to delete ALL records? (yes/no): " -r
    if [[ $REPLY == "yes" ]]; then
        echo -e "${YELLOW}Deleting all records...${NC}"
        
        mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" << EOF
DELETE FROM \`$TABLE_NAME\`;
ALTER TABLE \`$TABLE_NAME\` AUTO_INCREMENT = 1;
SELECT COUNT(*) as 'Records After Deletion' FROM \`$TABLE_NAME\`;
EOF
        
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✓ Records deleted successfully${NC}"
        else
            echo -e "${RED}✗ Failed to delete records${NC}"
            exit 1
        fi
    else
        echo -e "${YELLOW}Operation cancelled${NC}"
    fi
}

# Function to run web interface
run_web_interface() {
    echo -e "${BLUE}Starting web interface...${NC}"
    echo "Open your browser and go to:"
    echo "http://ttpl-rt-234-php83.local/dowayne_last_testing/delete_ad_orders.php"
    echo ""
    echo "Press Ctrl+C to stop the web server"
    
    # Start PHP built-in server
    cd /var/www/ttpl-rt-234-php83.local/public/dowayne_last_testing
    php -S localhost:8000
}

# Function to run CLI command
run_cli_command() {
    echo -e "${BLUE}Running Joomla CLI command...${NC}"
    
    cd /var/www/ttpl-rt-234-php83.local/public/dowayne_last_testing
    
    # Check if CLI command exists
    if [ -f "cli/DeleteOrdersCommand.php" ]; then
        php cli/joomla.php socialads:delete-orders --help
    else
        echo -e "${RED}CLI command file not found. Please check the file path.${NC}"
    fi
}

# Main script logic
case "${1:-}" in
    "web")
        run_web_interface
        ;;
    "sql")
        run_sql_deletion
        ;;
    "cli")
        run_cli_command
        ;;
    "backup")
        create_backup
        ;;
    "info")
        show_info
        ;;
    "help"|"-h"|"--help")
        show_usage
        ;;
    "")
        echo -e "${YELLOW}No option specified. Showing usage:${NC}"
        echo ""
        show_usage
        ;;
    *)
        echo -e "${RED}Unknown option: $1${NC}"
        echo ""
        show_usage
        exit 1
        ;;
esac

echo ""
echo -e "${BLUE}Script completed.${NC}"

