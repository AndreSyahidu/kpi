# KPI Dashboard - Production Deployment Guide

Complete guide for deploying the KPI Dashboard plugin to production environments.

---

## Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Deployment Methods](#deployment-methods)
3. [Production Configuration](#production-configuration)
4. [Post-Deployment Steps](#post-deployment-steps)
5. [Monitoring](#monitoring)
6. [Rollback Procedures](#rollback-procedures)
7. [Performance Tuning](#performance-tuning)

---

## Pre-Deployment Checklist

### Before You Deploy

- [ ] **Build frontend for production**
  ```bash
  cd kpi-dashboard
  npm install --legacy-peer-deps
  npm run build
  ```

- [ ] **Verify build output**
  ```bash
  ls -la assets/dist/assets/
  # Should see: main-*.js and main-*.css
  ```

- [ ] **Test on staging environment**
  - Install plugin on staging server
  - Test all core features
  - Verify API endpoints
  - Check frontend rendering
  - Test authentication flow
  - Verify database operations

- [ ] **Database backup**
  ```bash
  # Backup your database before deployment
  wp db export backup-$(date +%Y%m%d).sql
  ```

- [ ] **Security review**
  - Change default admin password
  - Review user permissions
  - Check JWT secret key
  - Verify HTTPS is enabled
  - Review file permissions

- [ ] **Performance check**
  - Ensure PHP 8.1+
  - Verify MySQL 8.0+
  - Check memory limits (min 256MB)
  - Test on production-like server

---

## Deployment Methods

### Method 1: WordPress Admin Upload (Recommended for Small Sites)

**Step 1: Create deployment package**
```bash
cd kpi-dashboard
./scripts/setup.sh package
```

This creates a ZIP file: `kpi-dashboard-YYYYMMDD-HHMMSS.zip`

**Step 2: Upload to WordPress**
1. Login to WordPress admin
2. Go to **Plugins > Add New**
3. Click **Upload Plugin**
4. Choose the ZIP file
5. Click **Install Now**
6. Click **Activate Plugin**

**Step 3: Verify installation**
- Go to **KPI Dashboard > System Info**
- Check all tables exist
- Verify upload directory is writable

---

### Method 2: FTP/SFTP Upload

**Step 1: Build locally**
```bash
npm install --legacy-peer-deps
npm run build
```

**Step 2: Upload via FTP**
```
Upload entire kpi-dashboard folder to:
/wp-content/plugins/kpi-dashboard/
```

**Step 3: Activate in WordPress**
1. Login to WordPress admin
2. Go to **Plugins**
3. Find "KPI Dashboard"
4. Click **Activate**

---

### Method 3: Git Deployment (Recommended for Large Sites)

**Step 1: Set up Git on server**
```bash
cd /path/to/wp-content/plugins/
git clone <repository-url> kpi-dashboard
cd kpi-dashboard
```

**Step 2: Build on server**
```bash
npm install --legacy-peer-deps
npm run build
```

**Or** upload pre-built assets:
```bash
# Build locally
npm run build

# Upload only dist folder
rsync -avz assets/dist/ user@server:/path/to/wp-content/plugins/kpi-dashboard/assets/dist/
```

**Step 3: Set permissions**
```bash
chmod 755 /path/to/wp-content/plugins/kpi-dashboard
chmod 644 kpi-dashboard/*.php
```

**Step 4: Activate plugin**
```bash
wp plugin activate kpi-dashboard
```

---

### Method 4: Automated Deployment (CI/CD)

**GitHub Actions Example:**
```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2

      - name: Setup Node.js
        uses: actions/setup-node@v2
        with:
          node-version: '18'

      - name: Install dependencies
        run: npm install --legacy-peer-deps

      - name: Build frontend
        run: npm run build

      - name: Deploy to server
        uses: easingthemes/ssh-deploy@main
        env:
          SSH_PRIVATE_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
          REMOTE_HOST: ${{ secrets.REMOTE_HOST }}
          REMOTE_USER: ${{ secrets.REMOTE_USER }}
          TARGET: /path/to/wp-content/plugins/kpi-dashboard
```

---

## Production Configuration

### 1. WordPress Configuration

**wp-config.php settings:**
```php
// Disable debug mode in production
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

// Security enhancements
define('DISALLOW_FILE_EDIT', true);
define('FORCE_SSL_ADMIN', true);

// Performance optimizations
define('WP_MEMORY_LIMIT', '512M');
define('WP_MAX_MEMORY_LIMIT', '512M');

// Optional: Custom JWT secret
define('KPI_DASHBOARD_JWT_SECRET', 'your-256-bit-secret-key');
```

### 2. PHP Configuration

**Recommended php.ini settings:**
```ini
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 20M
post_max_size = 20M
max_input_vars = 3000
```

### 3. Web Server Configuration

#### Apache (.htaccess)

```apache
# Gzip compression
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json
</IfModule>

# Browser caching
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType application/javascript "access plus 1 year"
  ExpiresByType text/css "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/webp "access plus 1 year"
</IfModule>

# Security headers
<IfModule mod_headers.c>
  Header set X-Content-Type-Options "nosniff"
  Header set X-Frame-Options "SAMEORIGIN"
  Header set X-XSS-Protection "1; mode=block"
  Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;

    # SSL configuration
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    # Gzip compression
    gzip on;
    gzip_types text/css application/javascript application/json;
    gzip_min_length 1000;

    # Browser caching
    location ~* \.(js|css|png|jpg|jpeg|gif|webp|svg|woff|woff2|ttf)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Security headers
    add_header X-Content-Type-Options "nosniff";
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";

    # WordPress configuration
    root /path/to/wordpress;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 4. Database Optimization

```bash
# Optimize tables
wp db optimize

# Add indexes (if not already created by plugin)
wp db query "
  ALTER TABLE wp_kpi_data ADD INDEX idx_period (period_start, period_end);
  ALTER TABLE wp_kpi_data ADD INDEX idx_status (status);
  ALTER TABLE wp_kpi_notifications ADD INDEX idx_read (is_read);
"
```

### 5. Plugin Settings

After activation, configure in WordPress admin:

**KPI Dashboard > Settings:**
- Company Name: Your company name
- Company Logo URL: Full URL to logo
- Primary Color: Brand color (hex)
- Secondary Color: Accent color (hex)
- Data Retention: 3 years (or as needed)

### 6. SSL Certificate

**Install SSL certificate (Let's Encrypt):**
```bash
# Install certbot
sudo apt-get install certbot

# Apache
sudo certbot --apache -d yourdomain.com

# Nginx
sudo certbot --nginx -d yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

---

## Post-Deployment Steps

### 1. Initial Configuration

**Access admin panel:**
```
https://yourdomain.com/wp-admin
```

**Go to KPI Dashboard:**
```
Dashboard > KPI Dashboard
```

### 2. Change Default Password

**Critical: Change admin password immediately!**

```bash
# Via WP-CLI
wp kpi-dashboard create-user \
  --username=superadmin \
  --email=admin@yourcompany.com \
  --name="Super Administrator" \
  --role=super_admin \
  --password=your-strong-password

# Then delete default admin user via dashboard
```

Or login to dashboard and change password:
1. Go to `https://yourdomain.com/kpi`
2. Login with admin/admin
3. Go to Settings/Profile
4. Change password

### 3. Create Department Structure

1. Go to **Departments** section
2. Modify default departments as needed
3. Create hierarchy (if applicable)
4. Assign department heads

### 4. Create Positions

1. Go to **Positions** section
2. Create positions for each department
3. Set position levels (Director, Manager, Senior, Staff)

### 5. Create Users

**Option A: Via Dashboard**
1. Go to **Users** section
2. Click "Add New User"
3. Fill in details
4. Assign department and position

**Option B: Via WP-CLI**
```bash
wp kpi-dashboard create-user \
  --username=johndoe \
  --email=john@company.com \
  --name="John Doe" \
  --role=staff \
  --department=2 \
  --position=5
```

**Option C: Bulk Import (CSV)**
Create CSV file and import via custom script.

### 6. Define KPIs

1. Go to **KPIs** section
2. Create KPI definitions
3. Set targets and measurement types
4. Assign to departments/positions/users

### 7. Set Up Notifications

1. Go to **Settings > Notifications**
2. Configure email settings
3. Set up alert rules
4. Test notification delivery

### 8. Generate Test Data (Optional)

**For testing only:**
```bash
# Via WP-CLI
wp kpi-dashboard generate-data

# Via Admin
Go to KPI Dashboard > System Info > Dummy Data Generator
```

### 9. Configure Scheduled Tasks

**Set up WordPress cron:**
```bash
# Add to crontab for better reliability
*/15 * * * * wget -q -O - https://yourdomain.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1
```

Or use WP-CLI:
```bash
*/15 * * * * cd /path/to/wordpress && wp cron event run --all
```

### 10. Test All Features

- [ ] Login/logout functionality
- [ ] User creation and management
- [ ] Department and position management
- [ ] KPI creation and assignment
- [ ] Data entry and submission
- [ ] Approval workflow
- [ ] Analytics dashboard
- [ ] Report generation
- [ ] Notification system
- [ ] Password reset flow

---

## Monitoring

### 1. Error Monitoring

**Enable logging for errors only:**
```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

**Check logs:**
```bash
tail -f /path/to/wp-content/debug.log
tail -f /path/to/kpi-dashboard/logs/kpi-*.log
```

### 2. Performance Monitoring

**Database queries:**
```bash
# Monitor slow queries
wp db query "SHOW FULL PROCESSLIST"
```

**Server resources:**
```bash
# CPU and memory usage
top
htop

# Disk usage
df -h
```

### 3. User Activity Monitoring

**Audit logs:**
- Check **KPI Dashboard > Audit Logs** in admin
- Monitor failed login attempts
- Review permission changes

### 4. Uptime Monitoring

Use external monitoring services:
- Pingdom
- UptimeRobot
- New Relic
- DataDog

**Monitor endpoints:**
- `https://yourdomain.com/kpi` - Dashboard
- `https://yourdomain.com/wp-json/kpi/v1/auth/me` - API health

### 5. Backup Verification

**Automated backups:**
```bash
# Database backup
0 2 * * * wp db export /backups/kpi-db-$(date +\%Y\%m\%d).sql

# File backup
0 3 * * * tar -czf /backups/kpi-files-$(date +\%Y\%m\%d).tar.gz /path/to/wp-content/plugins/kpi-dashboard
```

**Test restore:**
```bash
# Monthly restore test
wp db import /backups/kpi-db-YYYYMMDD.sql --network
```

---

## Rollback Procedures

### Quick Rollback

**If deployment fails:**

**Step 1: Deactivate plugin**
```bash
wp plugin deactivate kpi-dashboard
```

**Step 2: Restore previous version**
```bash
# Via FTP: replace plugin folder with backup
# Via Git:
cd /path/to/kpi-dashboard
git checkout previous-tag
```

**Step 3: Restore database (if needed)**
```bash
wp db import backup-YYYYMMDD.sql
```

**Step 4: Reactivate**
```bash
wp plugin activate kpi-dashboard
```

### Database Rollback

**If database structure changed:**
```bash
# Drop all plugin tables
wp kpi-dashboard reset --yes

# Restore from backup
wp db import backup-YYYYMMDD.sql
```

### Partial Rollback

**Rollback frontend only:**
```bash
# Replace dist folder
rm -rf assets/dist
cp -r /backup/assets/dist assets/
```

**Rollback backend only:**
```bash
# Replace PHP files
rsync -av /backup/includes/ includes/
```

---

## Performance Tuning

### 1. Object Caching

**Install Redis:**
```bash
sudo apt-get install redis-server php-redis
wp plugin install redis-cache --activate
wp redis enable
```

**Or Memcached:**
```bash
sudo apt-get install memcached php-memcached
```

### 2. Database Optimization

**Add composite indexes:**
```sql
ALTER TABLE wp_kpi_data
  ADD INDEX idx_composite (user_id, kpi_id, period_start);

ALTER TABLE wp_kpi_notifications
  ADD INDEX idx_user_read (user_id, is_read);
```

**Optimize tables regularly:**
```bash
# Weekly optimization
0 1 * * 0 wp db optimize
```

### 3. CDN Integration

**CloudFlare setup:**
1. Add site to CloudFlare
2. Update DNS records
3. Enable caching for static assets
4. Set up page rules:
   - Cache `/wp-content/plugins/kpi-dashboard/assets/*`
   - Bypass cache for `/wp-json/kpi/*`

**AWS CloudFront:**
```bash
# Upload assets to S3
aws s3 sync assets/dist/ s3://your-bucket/kpi-dashboard/

# Create CloudFront distribution
# Point to S3 bucket
```

### 4. Image Optimization

**Optimize company logo:**
```bash
# Use WebP format
cwebp logo.png -q 80 -o logo.webp

# Or use optimization service
```

### 5. PHP Opcache

**Enable opcache (php.ini):**
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### 6. Lazy Loading

Already implemented in React frontend using code splitting.

### 7. Database Connection Pooling

**For high traffic sites:**
```php
// wp-config.php
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// Consider using ProxySQL for connection pooling
```

---

## Security Hardening

### 1. File Permissions

```bash
# WordPress root
chmod 755 /path/to/wordpress

# wp-config.php
chmod 440 wp-config.php

# Plugin files
find kpi-dashboard -type d -exec chmod 755 {} \;
find kpi-dashboard -type f -exec chmod 644 {} \;

# Writable directories
chmod 755 wp-content/uploads
chmod 755 kpi-dashboard/logs
```

### 2. Security Plugins

**Recommended:**
- Wordfence Security
- Sucuri Security
- iThemes Security

### 3. Rate Limiting

**Nginx rate limiting:**
```nginx
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;

location /wp-json/kpi/v1/ {
    limit_req zone=api burst=20 nodelay;
}
```

### 4. Regular Updates

```bash
# Update WordPress core
wp core update

# Update plugins
wp plugin update --all

# Update PHP
sudo apt-get update && sudo apt-get upgrade php8.1
```

---

## Troubleshooting

### Common Issues

**Issue: Frontend not loading**
- **Solution:** Rebuild frontend
  ```bash
  npm run build
  ```

**Issue: API 404 errors**
- **Solution:** Flush permalinks
  ```bash
  wp rewrite flush
  ```

**Issue: Database tables missing**
- **Solution:** Reinstall tables
  ```bash
  wp kpi-dashboard install
  ```

**Issue: Slow performance**
- **Solution:** Enable caching, optimize database
  ```bash
  wp cache flush
  wp db optimize
  ```

---

## Support Checklist

- [ ] Production deployment completed
- [ ] Default password changed
- [ ] SSL certificate installed
- [ ] Backups configured
- [ ] Monitoring set up
- [ ] Performance optimized
- [ ] Security hardened
- [ ] All features tested
- [ ] Users trained
- [ ] Documentation provided

---

## Deployment Timeline

**Recommended deployment schedule:**

1. **Day 1:** Staging deployment and testing
2. **Day 2-3:** User acceptance testing
3. **Day 4:** Production deployment (off-peak hours)
4. **Day 5-7:** Monitoring and optimization
5. **Week 2:** User training
6. **Month 1:** Performance review

---

## Conclusion

Follow this guide for a successful production deployment of the KPI Dashboard plugin. Always test thoroughly on staging before deploying to production.

For additional support, refer to:
- Installation Guide: `docs/INSTALLATION.md`
- API Documentation: `docs/API.md`
- Implementation Summary: `IMPLEMENTATION_SUMMARY.md`

**Remember:**
- Always backup before deployment
- Test on staging first
- Deploy during off-peak hours
- Monitor closely after deployment
- Have rollback plan ready
