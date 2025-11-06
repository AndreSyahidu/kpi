# KPI Dashboard - Installation Guide

Complete installation and setup guide for the KPI Dashboard WordPress plugin.

---

## Table of Contents

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Initial Setup](#initial-setup)
5. [Development Setup](#development-setup)
6. [Production Deployment](#production-deployment)
7. [Troubleshooting](#troubleshooting)

---

## Requirements

### Minimum Requirements

- **WordPress:** 6.4 or higher
- **PHP:** 8.1 or higher
- **MySQL:** 8.0 or higher
- **Node.js:** 18.0 or higher (for frontend development)
- **npm:** 9.0 or higher

### PHP Extensions Required

- `mysqli` - MySQL database connection
- `json` - JSON processing
- `mbstring` - Multibyte string handling
- `openssl` - JWT token encryption
- `curl` - HTTP requests (optional, for external integrations)

### Server Requirements

- **Memory Limit:** Minimum 256MB (512MB recommended)
- **Upload Size:** Minimum 10MB
- **Execution Time:** Minimum 60 seconds
- **Disk Space:** Minimum 50MB for plugin + data storage

---

## Installation

### Method 1: WordPress Admin (Recommended for Production)

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Navigate to **Plugins > Add New**
4. Click **Upload Plugin**
5. Choose the `kpi-dashboard.zip` file
6. Click **Install Now**
7. After installation, click **Activate Plugin**

### Method 2: Manual Installation

1. Download and extract the plugin ZIP file
2. Upload the `kpi-dashboard` folder to `/wp-content/plugins/`
3. Go to **Plugins** in WordPress admin
4. Find "KPI Dashboard" and click **Activate**

### Method 3: Git Clone (For Development)

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone <repository-url> kpi-dashboard
cd kpi-dashboard
```

---

## Configuration

### 1. Database Setup

The plugin automatically creates all required database tables on activation. You can verify in WordPress admin:

1. Go to **KPI Dashboard > System Info**
2. Check **Database Tables** section
3. All 16 tables should show as "✅ All tables exist"

**Tables Created:**
- `kpi_users` - User management
- `kpi_departments` - Department hierarchy
- `kpi_positions` - Position definitions
- `kpi_department_heads` - Department head assignments
- `kpi_definitions` - KPI definitions
- `kpi_assignments` - KPI user assignments
- `kpi_data` - KPI data entries
- `kpi_audit_logs` - Activity audit trail
- `kpi_notifications` - User notifications
- `kpi_alert_rules` - Alert configurations
- `kpi_user_notification_prefs` - Notification preferences
- `kpi_scheduled_reports` - Report schedules
- `kpi_report_history` - Generated reports
- `kpi_comments` - Data entry comments
- `kpi_settings` - System settings
- `kpi_sessions` - User sessions

### 2. Permalink Settings

The plugin requires pretty permalinks to be enabled:

1. Go to **Settings > Permalinks**
2. Select any option except "Plain"
3. Recommended: "Post name" structure
4. Click **Save Changes**

### 3. Upload Directory

Ensure the uploads directory is writable:

```bash
chmod 755 /wp-content/uploads
```

Verify in **KPI Dashboard > System Info** - should show "✅ Upload Directory Writable"

---

## Initial Setup

### 1. Default Admin Account

On plugin activation, a default admin account is created:

**Username:** `admin`
**Password:** `admin`

**⚠️ SECURITY WARNING:** Change this password immediately after first login!

### 2. Default Departments

Six default departments are created:
- Sales
- Marketing
- Finance
- IT
- HR
- Operations

You can modify these in the KPI Dashboard interface.

### 3. Access the Dashboard

The KPI Dashboard is accessible at:
```
https://yoursite.com/kpi
```

**Not in WordPress admin!** This is a standalone application.

### 4. First Login

1. Go to `https://yoursite.com/kpi`
2. Login with default credentials (admin/admin)
3. You'll be prompted to change your password
4. After login, you'll see the dashboard overview

### 5. Generate Test Data (Optional)

For testing and demonstration:

1. Go to WordPress admin: **KPI Dashboard > System Info**
2. Scroll to **Dummy Data Generator**
3. Click **Generate Dummy Data**
4. This creates:
   - 23 test users across all roles
   - Positions for all departments
   - Department-specific KPIs
   - 6 months of historical data
   - Sample notifications

**Test Accounts Created:**
- `john.doe` / `password123` - Additional Super Admin
- `sarah.sales` / `password123` - Sales Dept Head
- `mike.marketing` / `password123` - Marketing Dept Head
- Plus 20 more managers and staff

**⚠️ Note:** Only use dummy data in development/testing environments!

---

## Development Setup

### 1. Clone Repository

```bash
git clone <repository-url> kpi-dashboard
cd kpi-dashboard
```

### 2. Install Dependencies

```bash
# Install Node.js dependencies for frontend
npm install
```

### 3. Development Server

Run the Vite development server for hot-reloading:

```bash
npm run dev
```

This starts the dev server at `http://localhost:5173`

The plugin will automatically detect dev mode and proxy requests.

### 4. Watch for Changes

In development mode:
- Edit files in `assets/src/`
- Changes hot-reload automatically
- TypeScript compilation happens on-the-fly

### 5. Code Quality

```bash
# Run ESLint
npm run lint

# Type checking
npx tsc --noEmit
```

---

## Production Deployment

### 1. Build Frontend

Before deploying to production, build the React app:

```bash
npm run build
```

This creates optimized production files in `assets/dist/`:
- `assets/dist/main.js` - Compiled JavaScript bundle
- `assets/dist/main.css` - Compiled CSS bundle
- Minified and optimized for performance

### 2. Plugin Configuration

Ensure production settings:

```php
// In wp-config.php
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
```

### 3. Deploy to Server

**Option A: ZIP Upload**
```bash
# Create production ZIP (exclude dev files)
zip -r kpi-dashboard.zip kpi-dashboard/ \
  -x "kpi-dashboard/node_modules/*" \
  -x "kpi-dashboard/.git/*" \
  -x "kpi-dashboard/assets/src/*" \
  -x "kpi-dashboard/.gitignore"
```

Upload via WordPress admin or FTP.

**Option B: Direct Upload**
```bash
# Upload via FTP/SFTP
# Upload entire kpi-dashboard folder to /wp-content/plugins/
```

**Option C: Automated Deployment**
```bash
# Using rsync
rsync -avz --exclude 'node_modules' \
           --exclude '.git' \
           --exclude 'assets/src' \
           kpi-dashboard/ user@server:/path/to/wp-content/plugins/kpi-dashboard/
```

### 4. Activate Plugin

1. Log in to WordPress admin
2. Go to **Plugins**
3. Activate "KPI Dashboard"
4. Verify installation at **KPI Dashboard > System Info**

### 5. SSL Certificate (Recommended)

For security, enable HTTPS:
```bash
# Using Let's Encrypt
sudo certbot --apache -d yoursite.com
```

### 6. Server Optimization

**Apache:**
```apache
# Enable gzip compression
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/css application/javascript
</IfModule>

# Browser caching
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType application/javascript "access plus 1 year"
  ExpiresByType text/css "access plus 1 year"
</IfModule>
```

**Nginx:**
```nginx
# Gzip compression
gzip on;
gzip_types text/css application/javascript;

# Browser caching
location ~* \.(js|css)$ {
  expires 1y;
  add_header Cache-Control "public, immutable";
}
```

---

## Configuration Options

### Plugin Settings

Configure in WordPress admin: **KPI Dashboard > Settings**

**Available Settings:**
- **Company Name** - Displayed in dashboard header
- **Company Logo URL** - Full URL to logo image
- **Primary Color** - Main theme color (hex code)
- **Secondary Color** - Accent color (hex code)
- **Data Retention (Years)** - How long to keep historical data (1-10 years)

### Email Configuration

The plugin uses WordPress's built-in email system. For better email delivery:

**Option 1: SMTP Plugin**
Install WP Mail SMTP plugin and configure:
```
Host: smtp.gmail.com
Port: 587
Encryption: TLS
Authentication: Yes
```

**Option 2: SendGrid/Mailgun**
Use transactional email service for production

### Scheduled Tasks

The plugin uses WordPress cron for:
- Data retention cleanup (daily)
- Scheduled reports (as configured)
- Notification digests (if enabled)

Verify cron is working:
```bash
wp cron event list --url=yoursite.com
```

---

## Security Hardening

### 1. Change Default Password

**Critical:** Change the default admin password immediately!

1. Login to KPI Dashboard
2. Go to Profile/Settings
3. Change password to a strong one

### 2. File Permissions

Set appropriate permissions:
```bash
# Plugin files
chmod 644 kpi-dashboard/*.php
chmod 755 kpi-dashboard/includes

# Logs directory (if exists)
chmod 755 kpi-dashboard/logs
```

### 3. Disable Directory Listing

Add to `.htaccess`:
```apache
Options -Indexes
```

### 4. Database Security

Use strong database credentials in `wp-config.php`:
```php
define('DB_PASSWORD', 'strong_random_password_here');
```

### 5. JWT Secret

The plugin generates a random JWT secret on activation. You can manually set it in `wp-config.php`:
```php
define('KPI_DASHBOARD_JWT_SECRET', 'your-256-bit-secret-key-here');
```

Generate a secure secret:
```bash
openssl rand -base64 32
```

---

## Backup and Restore

### Database Backup

**Backup plugin data:**
```bash
# Backup all KPI tables
wp db export kpi-backup.sql --tables=$(wp db query "SHOW TABLES LIKE 'wp_kpi_%'" --skip-column-names)
```

**Restore:**
```bash
wp db import kpi-backup.sql
```

### File Backup

Backup plugin directory and uploads:
```bash
tar -czf kpi-backup-$(date +%Y%m%d).tar.gz \
  wp-content/plugins/kpi-dashboard \
  wp-content/uploads/kpi-*
```

---

## Updates

### Updating the Plugin

**Before updating:**
1. Backup your database
2. Backup plugin files
3. Test on staging environment

**Update methods:**

**Method 1: WordPress Admin**
1. Download new version
2. Go to **Plugins**
3. Deactivate KPI Dashboard
4. Delete old version
5. Upload new version
6. Activate

**Method 2: Manual**
```bash
# Backup first
cp -r kpi-dashboard kpi-dashboard.backup

# Replace with new version
rm -rf kpi-dashboard
unzip kpi-dashboard-new-version.zip

# Activate in WordPress admin
```

**After updating:**
1. Check **System Info** for any issues
2. Verify database tables
3. Test core functionality

---

## Performance Optimization

### 1. Object Caching

Install Redis or Memcached:
```bash
# Install Redis
sudo apt install redis-server php-redis

# Install WordPress Redis plugin
wp plugin install redis-cache --activate
```

### 2. Database Optimization

Run periodic optimization:
```bash
wp db optimize
```

### 3. CDN Integration

For better frontend performance:
1. Use CloudFlare or similar CDN
2. Cache static assets (JS/CSS)
3. Enable automatic minification

### 4. Increase PHP Limits

In `php.ini`:
```ini
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 20M
post_max_size = 20M
```

---

## Troubleshooting

### Plugin Won't Activate

**Error: "PHP version too low"**
- **Solution:** Upgrade to PHP 8.1 or higher
- Check current version: `php -v`

**Error: "Database tables creation failed"**
- **Solution:** Check database permissions
- Verify MySQL version: `mysql --version`

### Can't Access /kpi URL

**404 Error**
- **Solution:** Flush permalinks
- Go to **Settings > Permalinks** and click Save

**500 Error**
- **Solution:** Check error logs
- Look at: `/wp-content/debug.log`
- Enable debug: `define('WP_DEBUG', true);`

### Login Issues

**"Invalid credentials" on correct password**
- **Solution:** Clear browser cache and cookies
- Check if user is active in database

**Token expired immediately**
- **Solution:** Check server time synchronization
- Verify: `date` should show correct time

### Frontend Not Loading

**Blank page at /kpi**
- **Solution:** Rebuild frontend
- Run: `npm run build`
- Check if `assets/dist/main.js` exists

**Vite dev server not working**
- **Solution:** Check port 5173 is not in use
- Try: `npm run dev -- --port 5174`

### Database Issues

**"Table doesn't exist" errors**
- **Solution:** Deactivate and reactivate plugin
- Or manually run database setup

**Slow queries**
- **Solution:** Rebuild indexes
- Run: `wp db query "SHOW INDEX FROM wp_kpi_data"`

### Email Not Sending

**Password reset emails not received**
- **Solution:** Install WP Mail SMTP
- Test email: `wp shell` then `wp_mail('test@test.com', 'Test', 'Body')`

### Permission Errors

**"Insufficient permissions" in API**
- **Solution:** Check user role in database
- Verify `role` column in `kpi_users` table

---

## Uninstallation

### Remove Plugin Data

**⚠️ WARNING:** This will permanently delete all KPI data!

1. Go to **Plugins**
2. Deactivate KPI Dashboard
3. Click **Delete**
4. Confirm deletion

This removes:
- All plugin files
- All database tables
- All uploaded files
- All settings

**To keep data:**
- Only deactivate, don't delete
- Backup database before uninstalling

---

## Getting Help

### Documentation

- **API Documentation:** See `docs/API.md`
- **Implementation Summary:** See `IMPLEMENTATION_SUMMARY.md`
- **README:** See `README.md`

### Support Resources

- Check plugin logs: `kpi-dashboard/logs/`
- WordPress debug log: `wp-content/debug.log`
- Browser console for frontend issues
- Network tab for API issues

### Common Log Files

```
wp-content/debug.log - WordPress errors
kpi-dashboard/logs/kpi-YYYY-MM-DD.log - Plugin logs
```

Enable logging:
```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

---

## Next Steps

After successful installation:

1. ✅ Change default admin password
2. ✅ Configure company settings
3. ✅ Set up departments and positions
4. ✅ Create user accounts
5. ✅ Define KPIs for each department
6. ✅ Assign KPIs to users
7. ✅ Start entering data
8. ✅ Configure notification preferences
9. ✅ Set up scheduled reports (if needed)
10. ✅ Train users on the system

### User Training

Provide users with:
- Login credentials
- Basic navigation guide
- How to enter KPI data
- How to check notifications
- How to view reports

---

## Version History

**v1.0.0** - Initial Release
- Complete KPI management system
- User hierarchy and permissions
- Data entry and approval workflow
- Analytics and reporting
- Notification system
- React frontend with Material-UI

---

## License

This plugin is proprietary software developed for MBD Corp.

**Copyright © 2024 MBD Corp. All rights reserved.**

---

## Credits

Built with:
- WordPress 6.4+
- React 18
- Material-UI 5
- TypeScript 5
- Vite 5
- ApexCharts 3
- Zustand 4
