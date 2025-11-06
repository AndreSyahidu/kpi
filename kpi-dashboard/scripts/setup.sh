#!/bin/bash

##############################################################################
# KPI Dashboard - Setup Script
#
# This script helps with initial setup and common maintenance tasks
# for the KPI Dashboard WordPress plugin.
#
# Usage: ./scripts/setup.sh [command]
##############################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_DIR="$(dirname "$SCRIPT_DIR")"

##############################################################################
# Helper Functions
##############################################################################

print_header() {
    echo -e "${BLUE}"
    echo "============================================================================"
    echo "  KPI Dashboard - $1"
    echo "============================================================================"
    echo -e "${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

check_command() {
    if ! command -v "$1" &> /dev/null; then
        print_error "$1 is not installed"
        return 1
    fi
    return 0
}

##############################################################################
# Check Requirements
##############################################################################

check_requirements() {
    print_header "Checking Requirements"

    local all_good=true

    # Check Node.js
    if check_command node; then
        NODE_VERSION=$(node --version)
        print_success "Node.js: $NODE_VERSION"
    else
        print_error "Node.js is required (18.0 or higher)"
        all_good=false
    fi

    # Check npm
    if check_command npm; then
        NPM_VERSION=$(npm --version)
        print_success "npm: v$NPM_VERSION"
    else
        print_error "npm is required"
        all_good=false
    fi

    # Check PHP
    if check_command php; then
        PHP_VERSION=$(php -v | head -n 1 | cut -d ' ' -f 2)
        print_success "PHP: $PHP_VERSION"
    else
        print_error "PHP is required (8.1 or higher)"
        all_good=false
    fi

    # Check WP-CLI (optional)
    if check_command wp; then
        WP_VERSION=$(wp --version | cut -d ' ' -f 2)
        print_success "WP-CLI: $WP_VERSION"
    else
        print_warning "WP-CLI not found (optional but recommended)"
    fi

    # Check git
    if check_command git; then
        print_success "Git: installed"
    else
        print_warning "Git not found (optional)"
    fi

    echo ""

    if [ "$all_good" = false ]; then
        print_error "Some requirements are missing. Please install them first."
        exit 1
    fi

    print_success "All requirements satisfied!"
    echo ""
}

##############################################################################
# Install Dependencies
##############################################################################

install_deps() {
    print_header "Installing Dependencies"

    cd "$PLUGIN_DIR"

    if [ -f "package.json" ]; then
        print_info "Installing Node.js dependencies..."
        npm install
        print_success "Dependencies installed successfully!"
    else
        print_error "package.json not found"
        exit 1
    fi

    echo ""
}

##############################################################################
# Build Frontend
##############################################################################

build_frontend() {
    print_header "Building Frontend"

    cd "$PLUGIN_DIR"

    print_info "Running TypeScript compiler and Vite build..."
    npm run build

    if [ -f "assets/dist/main.js" ]; then
        print_success "Frontend built successfully!"

        # Show file sizes
        JS_SIZE=$(du -h assets/dist/main.js | cut -f1)
        CSS_SIZE=$(du -h assets/dist/main.css | cut -f1)
        print_info "JavaScript bundle: $JS_SIZE"
        print_info "CSS bundle: $CSS_SIZE"
    else
        print_error "Build failed - main.js not found"
        exit 1
    fi

    echo ""
}

##############################################################################
# Development Mode
##############################################################################

dev_mode() {
    print_header "Starting Development Server"

    cd "$PLUGIN_DIR"

    print_info "Starting Vite dev server on http://localhost:5173"
    print_info "Press Ctrl+C to stop"
    echo ""

    npm run dev
}

##############################################################################
# Create Production Package
##############################################################################

create_package() {
    print_header "Creating Production Package"

    cd "$PLUGIN_DIR"

    # Build first
    print_info "Building frontend..."
    npm run build

    # Create package directory
    PACKAGE_NAME="kpi-dashboard-$(date +%Y%m%d-%H%M%S)"
    PACKAGE_DIR="/tmp/$PACKAGE_NAME"

    print_info "Creating package in $PACKAGE_DIR..."

    # Copy files
    mkdir -p "$PACKAGE_DIR"

    # Copy plugin files (exclude dev files)
    rsync -av \
        --exclude 'node_modules' \
        --exclude '.git' \
        --exclude '.gitignore' \
        --exclude 'assets/src' \
        --exclude 'scripts' \
        --exclude 'logs' \
        --exclude '.DS_Store' \
        --exclude '*.log' \
        "$PLUGIN_DIR/" "$PACKAGE_DIR/"

    # Create ZIP
    cd /tmp
    ZIP_FILE="$PACKAGE_NAME.zip"
    zip -r "$ZIP_FILE" "$PACKAGE_NAME" -q

    # Move to plugin directory
    mv "$ZIP_FILE" "$PLUGIN_DIR/"

    # Cleanup
    rm -rf "$PACKAGE_DIR"

    ZIP_SIZE=$(du -h "$PLUGIN_DIR/$ZIP_FILE" | cut -f1)
    print_success "Package created: $ZIP_FILE ($ZIP_SIZE)"
    print_info "Ready for deployment!"

    echo ""
}

##############################################################################
# Verify Installation
##############################################################################

verify_installation() {
    print_header "Verifying Installation"

    cd "$PLUGIN_DIR"

    # Check main plugin file
    if [ -f "kpi-dashboard.php" ]; then
        print_success "Main plugin file exists"
    else
        print_error "Main plugin file not found"
    fi

    # Check includes directory
    if [ -d "includes" ]; then
        print_success "Includes directory exists"
    else
        print_error "Includes directory not found"
    fi

    # Check assets
    if [ -d "assets" ]; then
        print_success "Assets directory exists"
    else
        print_error "Assets directory not found"
    fi

    # Check if built
    if [ -f "assets/dist/main.js" ]; then
        print_success "Frontend is built"
    else
        print_warning "Frontend not built (run: npm run build)"
    fi

    # Check node_modules
    if [ -d "node_modules" ]; then
        print_success "Dependencies installed"
    else
        print_warning "Dependencies not installed (run: npm install)"
    fi

    echo ""
}

##############################################################################
# Clean Build Files
##############################################################################

clean_build() {
    print_header "Cleaning Build Files"

    cd "$PLUGIN_DIR"

    print_info "Removing build artifacts..."

    # Remove dist
    if [ -d "assets/dist" ]; then
        rm -rf assets/dist
        print_success "Removed assets/dist"
    fi

    # Remove node_modules
    if [ -d "node_modules" ]; then
        rm -rf node_modules
        print_success "Removed node_modules"
    fi

    # Remove logs
    if [ -d "logs" ]; then
        rm -rf logs/*.log
        print_success "Removed log files"
    fi

    print_success "Clean complete!"
    echo ""
}

##############################################################################
# Database Operations (requires WP-CLI)
##############################################################################

check_database() {
    print_header "Checking Database"

    if ! check_command wp; then
        print_error "WP-CLI is required for database operations"
        exit 1
    fi

    # Check if WordPress is found
    if ! wp core is-installed 2>/dev/null; then
        print_error "WordPress installation not found in current directory"
        print_info "Run this command from your WordPress root directory"
        exit 1
    fi

    # Check tables
    print_info "Checking KPI Dashboard tables..."

    TABLES=(
        "kpi_users"
        "kpi_departments"
        "kpi_positions"
        "kpi_definitions"
        "kpi_data"
        "kpi_notifications"
        "kpi_audit_logs"
        "kpi_sessions"
    )

    local all_exist=true
    for table in "${TABLES[@]}"; do
        if wp db query "SHOW TABLES LIKE '${table}'" --skip-column-names | grep -q "$table"; then
            print_success "Table $table exists"
        else
            print_error "Table $table is missing"
            all_exist=false
        fi
    done

    echo ""

    if [ "$all_exist" = true ]; then
        print_success "All database tables exist!"
    else
        print_warning "Some tables are missing. Try deactivating and reactivating the plugin."
    fi

    echo ""
}

##############################################################################
# Show System Info
##############################################################################

show_info() {
    print_header "System Information"

    echo "Plugin Directory: $PLUGIN_DIR"
    echo ""

    # Node.js
    if check_command node; then
        echo "Node.js: $(node --version)"
    fi

    # npm
    if check_command npm; then
        echo "npm: v$(npm --version)"
    fi

    # PHP
    if check_command php; then
        echo "PHP: $(php -v | head -n 1 | cut -d ' ' -f 2)"
    fi

    # WP-CLI
    if check_command wp; then
        echo "WP-CLI: $(wp --version | cut -d ' ' -f 2)"
    fi

    echo ""

    # File sizes
    if [ -f "assets/dist/main.js" ]; then
        echo "Build Status: ✓ Built"
        echo "JavaScript: $(du -h assets/dist/main.js | cut -f1)"
        echo "CSS: $(du -h assets/dist/main.css | cut -f1)"
    else
        echo "Build Status: ✗ Not built"
    fi

    echo ""
}

##############################################################################
# Help
##############################################################################

show_help() {
    print_header "KPI Dashboard Setup Script"

    echo "Usage: ./scripts/setup.sh [command]"
    echo ""
    echo "Commands:"
    echo "  check         Check system requirements"
    echo "  install       Install dependencies (npm install)"
    echo "  build         Build frontend for production"
    echo "  dev           Start development server"
    echo "  package       Create production package (ZIP)"
    echo "  verify        Verify installation"
    echo "  clean         Clean build files"
    echo "  db-check      Check database tables (requires WP-CLI)"
    echo "  info          Show system information"
    echo "  help          Show this help message"
    echo ""
    echo "Common Workflows:"
    echo ""
    echo "  Initial Setup:"
    echo "    ./scripts/setup.sh check"
    echo "    ./scripts/setup.sh install"
    echo "    ./scripts/setup.sh build"
    echo ""
    echo "  Development:"
    echo "    ./scripts/setup.sh dev"
    echo ""
    echo "  Production Deploy:"
    echo "    ./scripts/setup.sh build"
    echo "    ./scripts/setup.sh package"
    echo ""
}

##############################################################################
# Main
##############################################################################

main() {
    # If no command, show help
    if [ $# -eq 0 ]; then
        show_help
        exit 0
    fi

    # Parse command
    case "$1" in
        check)
            check_requirements
            ;;
        install)
            check_requirements
            install_deps
            ;;
        build)
            build_frontend
            ;;
        dev)
            dev_mode
            ;;
        package)
            create_package
            ;;
        verify)
            verify_installation
            ;;
        clean)
            clean_build
            ;;
        db-check)
            check_database
            ;;
        info)
            show_info
            ;;
        help|--help|-h)
            show_help
            ;;
        *)
            print_error "Unknown command: $1"
            echo ""
            show_help
            exit 1
            ;;
    esac
}

# Run main function
main "$@"
