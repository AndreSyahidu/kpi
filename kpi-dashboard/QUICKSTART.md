# KPI Dashboard - Quick Start Guide

Get up and running with the KPI Dashboard in under 10 minutes!

---

## ⚡ 5-Minute Setup

### Prerequisites

Before you begin, ensure you have:
- ✅ WordPress 6.4+ installed
- ✅ PHP 8.1+ available
- ✅ MySQL 8.0+ or MariaDB 10.6+
- ✅ Node.js 18+ (for building)

### Step 1: Install the Plugin (2 minutes)

**Option A: Upload ZIP**
1. Download the plugin ZIP file
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin"
4. Choose the ZIP file and click "Install Now"
5. Click "Activate"

**Option B: Git Clone**
```bash
cd wp-content/plugins/
git clone <repository-url> kpi-dashboard
cd kpi-dashboard
```

### Step 2: Build Assets (1 minute)

```bash
# Quick build
npm install --legacy-peer-deps
npm run build
```

**Or use the automated script:**
```bash
./scripts/setup.sh install
./scripts/setup.sh build
```

### Step 3: Activate Plugin (30 seconds)

1. Go to WordPress Admin → Plugins
2. Find "KPI Dashboard"
3. Click "Activate"
4. Plugin creates database tables automatically

### Step 4: Access Dashboard (30 seconds)

Navigate to:
```
https://yourdomain.com/kpi
```

**Login with default credentials:**
- Username: `admin`
- Password: `admin`

⚠️ **Change this password immediately!**

### Step 5: Generate Test Data (Optional - 1 minute)

**Via WordPress Admin:**
1. Go to **KPI Dashboard > System Info**
2. Scroll to **Dummy Data Generator**
3. Click **Generate Dummy Data**

**Or via WP-CLI:**
```bash
wp kpi-dashboard generate-data
```

This creates:
- 23 test users
- Sample KPIs for all departments
- 6 months of historical data

---

## 🎯 First Steps After Installation

### 1. Secure Your Installation

**Change Default Password:**
1. Login to dashboard at `/kpi`
2. Click your name (top right)
3. Go to Settings/Profile
4. Change password
5. Save changes

**Or create new admin user:**
```bash
wp kpi-dashboard create-user \
  --username=youradmin \
  --email=your@email.com \
  --name="Your Name" \
  --role=super_admin
```

Then delete the default admin user from the dashboard.

### 2. Configure Company Settings

1. Go to WordPress Admin → **KPI Dashboard → Settings**
2. Set your company name
3. Upload company logo URL
4. Set brand colors
5. Configure data retention period
6. Save settings

### 3. Set Up Your Structure

#### Create Departments
1. Go to dashboard → **Departments**
2. Modify the 6 default departments or create new ones
3. Set up hierarchy if needed
4. Assign department heads

#### Create Positions
1. Go to **Positions**
2. Create positions for each department
3. Set position levels (Director, Manager, Senior, Staff)

#### Create Users
1. Go to **Users**
2. Click "Add New User"
3. Fill in details
4. Assign department and position
5. Set role level

### 4. Define Your KPIs

1. Go to **KPIs**
2. Click "Add New KPI"
3. Set KPI details:
   - Name (e.g., "Monthly Sales Revenue")
   - Type (Department/Position/Personal)
   - Measurement (Number/Percentage/Currency)
   - Target value
   - Frequency (Daily/Weekly/Monthly)
4. Assign to users or departments
5. Save

### 5. Start Tracking

Users can now:
1. Login at `/kpi`
2. See their assigned KPIs
3. Enter data
4. Submit for approval
5. Track their performance

---

## 📊 Common Use Cases

### Use Case 1: Sales Department Tracking

**Goal:** Track monthly sales revenue for 10 sales staff

**Steps:**
1. Create KPI: "Monthly Sales Revenue"
   - Type: Position (Sales Executive)
   - Measurement: Currency (IDR)
   - Target: 50,000,000 per person
   - Frequency: Monthly

2. Assign to all sales staff with individual targets

3. Sales staff enter actual revenue each month

4. Manager reviews and approves

5. View analytics to see top performers

### Use Case 2: Department Performance Dashboard

**Goal:** Compare performance across 6 departments

**Steps:**
1. Create department-level KPIs for each dept
2. Assign department heads as approvers
3. Monthly data entry by dept heads
4. Use Analytics → Department Comparison
5. Generate executive reports

### Use Case 3: Individual Employee KPIs

**Goal:** Track custom KPIs for each employee

**Steps:**
1. Create Position-level KPIs (apply to all in position)
2. Or create Personal KPIs (specific to one person)
3. Employee enters data regularly
4. Manager approves submissions
5. Employee tracks progress in their dashboard

---

## 🔑 Key Features to Try

### Dashboard Overview
- **Location:** `/kpi` after login
- **Shows:** Quick stats, pending approvals, recent activity
- **Try:** Login and explore the overview

### Data Entry
- **Location:** Data Entry menu
- **Try:**
  1. Select a KPI
  2. Choose period
  3. Enter value
  4. Add notes
  5. Save as draft or submit for approval

### Approvals
- **Who:** Managers, Dept Heads, Super Admins
- **Try:**
  1. Go to Approvals menu
  2. See pending submissions
  3. Review data
  4. Approve or reject with notes

### Analytics
- **Location:** Analytics menu
- **Try:**
  1. View company overview
  2. Check best performers
  3. See department comparison
  4. Review trends

### Reports
- **Location:** Reports menu
- **Try:**
  1. Select report type
  2. Choose date range
  3. Pick format (CSV/JSON)
  4. Generate and download

---

## 🎨 Customization Tips

### Branding

**Logo:**
```
WordPress Admin → KPI Dashboard → Settings
→ Company Logo URL: https://your-logo-url.com/logo.png
```

**Colors:**
```
Primary Color: #1565C0 (Blue)
Secondary Color: #FF6B35 (Orange)
```

### Dark Mode
- Click moon icon in dashboard header
- Preference saved automatically

### Notifications
1. Go to Settings → Notifications
2. Enable/disable email notifications
3. Set preferences per notification type

---

## 💡 Tips for Success

### For Administrators

1. **Start Small:** Begin with one department, test the workflow
2. **Use Test Data:** Generate dummy data to familiarize yourself
3. **Set Clear Targets:** Make KPI targets realistic and achievable
4. **Regular Reviews:** Check analytics weekly
5. **Clean Data:** Remove test data before going live

### For Department Heads

1. **Review Approvals Daily:** Don't let submissions pile up
2. **Provide Feedback:** Use notes when approving/rejecting
3. **Monitor Trends:** Check department analytics weekly
4. **Coach Staff:** Help underperformers improve

### For Staff

1. **Enter Data Promptly:** Don't wait until deadline
2. **Be Accurate:** Double-check values before submitting
3. **Add Context:** Use notes to explain unusual values
4. **Track Progress:** Review your own analytics

---

## 🚀 Advanced Features

### Bulk Operations

**Bulk Data Entry:**
```bash
# Create multiple entries at once via API
curl -X POST /wp-json/kpi/v1/data/bulk \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "entries": [
      {"kpi_id": 1, "value": 1000000, "period_start": "2025-01-01"},
      {"kpi_id": 2, "value": 15.5, "period_start": "2025-01-01"}
    ]
  }'
```

**Bulk User Creation:**
```bash
# Via CSV import (custom script)
wp kpi-dashboard import-users users.csv
```

### Scheduled Reports

Set up automated weekly/monthly reports:
1. Go to Reports → Scheduled Reports
2. Configure frequency and recipients
3. Reports sent automatically via email

### API Integration

Access via REST API:
```javascript
// Get your KPIs
fetch('/wp-json/kpi/v1/kpis', {
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN'
  }
});
```

See full API docs: `docs/API.md`

---

## 🆘 Troubleshooting

### Common Issues

**Issue: Can't access /kpi URL**
- **Solution:** Flush permalinks
  ```bash
  wp rewrite flush
  ```

**Issue: Frontend not loading**
- **Solution:** Rebuild assets
  ```bash
  npm run build
  ```

**Issue: Login fails**
- **Solution:** Reset admin password
  ```bash
  wp kpi-dashboard reset --yes
  ```

**Issue: Database tables missing**
- **Solution:** Reinstall tables
  ```bash
  wp kpi-dashboard install
  ```

**Issue: Slow performance**
- **Solution:** Clear cache
  ```bash
  wp cache flush
  ```

### Getting Help

1. **Check Documentation:**
   - Installation Guide: `docs/INSTALLATION.md`
   - API Documentation: `docs/API.md`
   - Deployment Guide: `docs/DEPLOYMENT.md`

2. **System Info:**
   ```bash
   wp kpi-dashboard info
   ```

3. **Check Logs:**
   ```bash
   tail -f wp-content/debug.log
   tail -f kpi-dashboard/logs/kpi-*.log
   ```

4. **Contact Support:**
   - Email: support@mbdcorp.id
   - Website: https://www.mbdcorp.id

---

## 📚 Next Steps

After completing this quick start:

1. **Read Full Documentation:**
   - [Installation Guide](docs/INSTALLATION.md)
   - [API Documentation](docs/API.md)
   - [Deployment Guide](docs/DEPLOYMENT.md)

2. **Explore Features:**
   - Try all menu items
   - Test the approval workflow
   - Generate some reports
   - Check analytics

3. **Plan Your Rollout:**
   - Define your KPI strategy
   - Set up department structure
   - Create user accounts
   - Train your team

4. **Go Live:**
   - Remove test data
   - Set real targets
   - Start tracking!

---

## ✅ Checklist

Use this checklist to ensure proper setup:

- [ ] Plugin installed and activated
- [ ] Frontend built successfully
- [ ] Can access `/kpi` dashboard
- [ ] Default password changed
- [ ] Company settings configured
- [ ] Departments created/modified
- [ ] Positions defined
- [ ] Test users created
- [ ] Sample KPIs defined
- [ ] Test data entry completed
- [ ] Approval workflow tested
- [ ] Analytics reviewed
- [ ] Report generated
- [ ] Notifications working
- [ ] Test data cleared (before production)
- [ ] Real users created
- [ ] Production KPIs defined
- [ ] Team trained
- [ ] Ready to go live! 🎉

---

## 🎓 Training Resources

### For New Users

**15-Minute Introduction:**
1. Login to dashboard (2 min)
2. View your assigned KPIs (3 min)
3. Enter sample data (5 min)
4. Check your performance (3 min)
5. Explore settings (2 min)

### For Managers

**30-Minute Training:**
1. Understanding the dashboard (5 min)
2. Reviewing pending approvals (10 min)
3. Using analytics (10 min)
4. Generating reports (5 min)

### For Administrators

**1-Hour Workshop:**
1. System architecture (10 min)
2. User management (15 min)
3. KPI configuration (20 min)
4. Reports and analytics (10 min)
5. Troubleshooting (5 min)

---

## 🌟 Best Practices

1. **Regular Data Entry:** Set a schedule (e.g., every Monday)
2. **Timely Approvals:** Review within 24 hours
3. **Consistent Targets:** Don't change mid-period
4. **Clean Data:** Validate before submitting
5. **Regular Reviews:** Weekly analytics check
6. **Backup Regularly:** Daily database backups
7. **Monitor Performance:** Check server resources
8. **Update Regularly:** Keep plugin updated
9. **Train Users:** Ongoing education
10. **Gather Feedback:** Improve continuously

---

## 🎯 Success Metrics

Track these metrics to measure adoption:

- **User Adoption:** % of users logging in weekly
- **Data Completeness:** % of KPIs with current data
- **Approval Speed:** Average time from submit to approve
- **Report Usage:** Number of reports generated
- **Performance Improvement:** % increase in KPI achievement

---

**Ready to start? Let's go!** 🚀

For detailed documentation, see the full guides in the `/docs` folder.
