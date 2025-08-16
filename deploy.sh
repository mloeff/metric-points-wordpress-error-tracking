#!/bin/bash

# Metric Points Error Tracking Plugin Deployment Script
# This script helps deploy the plugin to a WordPress installation

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to check if directory exists
directory_exists() {
    [ -d "$1" ]
}

# Function to check if file exists
file_exists() {
    [ -f "$1" ]
}

# Function to get WordPress root directory
get_wp_root() {
    local current_dir="$PWD"
    
    while [ "$current_dir" != "/" ]; do
        if [ -f "$current_dir/wp-config.php" ]; then
            echo "$current_dir"
            return 0
        fi
        current_dir=$(dirname "$current_dir")
    done
    
    return 1
}

# Function to validate WordPress installation
validate_wp_installation() {
    local wp_root="$1"
    
    if [ ! -f "$wp_root/wp-config.php" ]; then
        print_error "wp-config.php not found in $wp_root"
        return 1
    fi
    
    if [ ! -d "$wp_root/wp-content" ]; then
        print_error "wp-content directory not found in $wp_root"
        return 1
    fi
    
    if [ ! -d "$wp_root/wp-content/plugins" ]; then
        print_error "plugins directory not found in $wp_root/wp-content"
        return 1
    fi
    
    print_success "WordPress installation validated"
    return 0
}

# Function to backup existing plugin
backup_existing_plugin() {
    local plugin_dir="$1"
    local backup_dir="$2"
    
    if directory_exists "$plugin_dir"; then
        local timestamp=$(date +%Y%m%d_%H%M%S)
        local backup_name="metric-points-error-tracking-backup-$timestamp"
        local backup_path="$backup_dir/$backup_name"
        
        print_status "Backing up existing plugin to $backup_path"
        cp -r "$plugin_dir" "$backup_path"
        print_success "Backup created: $backup_name"
    fi
}

# Function to deploy plugin
deploy_plugin() {
    local source_dir="$1"
    local target_dir="$2"
    
    print_status "Deploying plugin to $target_dir"
    
    # Create target directory if it doesn't exist
    mkdir -p "$target_dir"
    
    # Copy plugin files
    cp -r "$source_dir"/* "$target_dir/"
    
    # Set proper permissions
    chmod 755 "$target_dir"
    find "$target_dir" -type f -exec chmod 644 {} \;
    find "$target_dir" -type d -exec chmod 755 {} \;
    
    print_success "Plugin deployed successfully"
}

# Function to deploy test page
deploy_test_page() {
    local wp_root="$1"
    local test_file="$2"
    
    if file_exists "$test_file"; then
        local target_file="$wp_root/test-error-tracking.html"
        print_status "Deploying test page to $target_file"
        cp "$test_file" "$target_file"
        chmod 644 "$target_file"
        print_success "Test page deployed to $target_file"
        print_status "You can now visit: $(basename "$wp_root")/test-error-tracking.html"
    fi
}

# Function to show usage
show_usage() {
    echo "Usage: $0 [OPTIONS] [WORDPRESS_ROOT]"
    echo ""
    echo "Options:"
    echo "  -h, --help          Show this help message"
    echo "  -f, --force         Force deployment without confirmation"
    echo "  -b, --backup        Create backup of existing plugin"
    echo "  -t, --test-page     Deploy test page"
    echo "  -v, --verbose       Verbose output"
    echo ""
    echo "Arguments:"
    echo "  WORDPRESS_ROOT      Path to WordPress root directory (optional)"
    echo ""
    echo "Examples:"
    echo "  $0                           # Deploy to current directory or auto-detect"
    echo "  $0 /var/www/wordpress       # Deploy to specific WordPress directory"
    echo "  $0 -f -b -t                 # Force deploy with backup and test page"
}

# Main function
main() {
    local force_deploy=false
    local create_backup=false
    local deploy_test=false
    local verbose=false
    local wp_root=""
    
    # Parse command line arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            -h|--help)
                show_usage
                exit 0
                ;;
            -f|--force)
                force_deploy=true
                shift
                ;;
            -b|--backup)
                create_backup=true
                shift
                ;;
            -t|--test-page)
                deploy_test=true
                shift
                ;;
            -v|--verbose)
                verbose=true
                shift
                ;;
            -*)
                print_error "Unknown option: $1"
                show_usage
                exit 1
                ;;
            *)
                if [ -z "$wp_root" ]; then
                    wp_root="$1"
                else
                    print_error "Multiple WordPress root directories specified"
                    exit 1
                fi
                shift
                ;;
        esac
    done
    
    # Set verbose mode
    if [ "$verbose" = true ]; then
        set -x
    fi
    
    print_status "Metric Points Error Tracking Plugin Deployment Script"
    echo ""
    
    # Get WordPress root directory
    if [ -z "$wp_root" ]; then
        print_status "Auto-detecting WordPress root directory..."
        if wp_root=$(get_wp_root); then
            print_success "WordPress root detected: $wp_root"
        else
            print_error "Could not auto-detect WordPress root directory"
            print_status "Please specify the WordPress root directory as an argument"
            show_usage
            exit 1
        fi
    fi
    
    # Validate WordPress installation
    if ! validate_wp_installation "$wp_root"; then
        print_error "Invalid WordPress installation: $wp_root"
        exit 1
    fi
    
    # Define paths
    local script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    local plugin_name="metric-points-error-tracking"
    local source_dir="$script_dir"
    local target_dir="$wp_root/wp-content/plugins/$plugin_name"
    local backup_dir="$wp_root/wp-content/plugins"
    
    print_status "Plugin source: $source_dir"
    print_status "Plugin target: $target_dir"
    
    # Check if plugin already exists
    if directory_exists "$target_dir"; then
        if [ "$force_deploy" = false ]; then
            print_warning "Plugin already exists at $target_dir"
            echo -n "Do you want to overwrite it? (y/N): "
            read -r response
            if [[ ! "$response" =~ ^[Yy]$ ]]; then
                print_status "Deployment cancelled"
                exit 0
            fi
        fi
        
        # Create backup if requested
        if [ "$create_backup" = true ]; then
            backup_existing_plugin "$target_dir" "$backup_dir"
        fi
    fi
    
    # Deploy plugin
    deploy_plugin "$source_dir" "$target_dir"
    
    # Deploy test page if requested
    if [ "$deploy_test" = true ]; then
        local test_file="$script_dir/test-error-tracking.html"
        deploy_test_page "$wp_root" "$test_file"
    fi
    
    echo ""
    print_success "Deployment completed successfully!"
    echo ""
    print_status "Next steps:"
    echo "1. Go to WordPress admin > Plugins"
    echo "2. Activate 'Metric Points Error Tracking'"
    echo "3. Go to Settings > Error Tracking"
    echo "4. Configure your API key"
    echo "5. Test the connection"
    
    if [ "$deploy_test" = true ]; then
        echo "6. Visit your test page: $(basename "$wp_root")/test-error-tracking.html"
    fi
    
    echo ""
    print_status "For more information, see README.md and INSTALL.md"
}

# Run main function with all arguments
main "$@"
