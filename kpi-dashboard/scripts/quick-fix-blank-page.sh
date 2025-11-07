#!/bin/bash

##############################################################################
# KPI Dashboard - Quick Fix for Blank Page
#
# This script creates the assets/dist folder and copies pre-built files
# Use this if npm/node is not available on your server
##############################################################################

PLUGIN_DIR="/home/syahiduc/mbdcorp.id/wp-content/plugins/kpi-dashboard"

echo "========================================"
echo "KPI Dashboard - Quick Fix"
echo "========================================"
echo ""

# Check if we're in the right directory
if [ ! -f "$PLUGIN_DIR/kpi-dashboard.php" ]; then
    echo "❌ Error: Plugin not found at $PLUGIN_DIR"
    echo ""
    echo "Please update PLUGIN_DIR variable in this script"
    exit 1
fi

cd "$PLUGIN_DIR"

echo "✓ Plugin directory found"
echo ""

# Create assets/dist directory structure
echo "Creating directory structure..."
mkdir -p assets/dist/.vite
mkdir -p assets/dist/assets

echo "✓ Directories created"
echo ""

# Download pre-built files from repository or use backup
echo "========================================"
echo "NEXT STEPS:"
echo "========================================"
echo ""
echo "1. Download pre-built files from:"
echo "   https://github.com/your-repo/releases/latest/download/assets-dist.zip"
echo ""
echo "2. Or ask developer for 'assets-dist-prebuilt.tar.gz'"
echo ""
echo "3. Extract to: $PLUGIN_DIR/assets/"
echo ""
echo "4. Run: wp rewrite flush"
echo ""
echo "5. Access: https://www.mbdcorp.id/kpi"
echo ""
echo "========================================"
