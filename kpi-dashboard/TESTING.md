# Testing Guide - KPI Dashboard

Comprehensive testing documentation for the KPI Dashboard WordPress plugin.

---

## Table of Contents

1. [Testing Strategy](#testing-strategy)
2. [Manual Testing](#manual-testing)
3. [API Testing](#api-testing)
4. [Database Testing](#database-testing)
5. [Frontend Testing](#frontend-testing)
6. [Performance Testing](#performance-testing)
7. [Security Testing](#security-testing)
8. [Test Scenarios](#test-scenarios)

---

## Testing Strategy

### Testing Levels

1. **Unit Testing** - Individual functions and methods
2. **Integration Testing** - Component interactions
3. **System Testing** - End-to-end workflows
4. **User Acceptance Testing** - Real-world scenarios
5. **Performance Testing** - Load and stress testing
6. **Security Testing** - Vulnerability assessment

### Testing Tools

- **Manual Testing**: Browser, Postman, curl
- **PHP Testing**: PHPUnit (future implementation)
- **JavaScript Testing**: Jest, React Testing Library (future)
- **API Testing**: Postman, curl, HTTPie
- **Performance**: Chrome DevTools, Apache Bench
- **Security**: OWASP ZAP, Security scanners

---

## Manual Testing

### Pre-Testing Checklist

- [ ] WordPress 6.4+ installed
- [ ] PHP 8.1+ available
- [ ] MySQL 8.0+ running
- [ ] Plugin activated
- [ ] Frontend built (`npm run build`)
- [ ] Test data generated

### 1. Installation Testing

**Test: Fresh Installation**
```bash
# Deactivate plugin
wp plugin deactivate kpi-dashboard

# Drop tables
wp kpi-dashboard reset --yes

# Reactivate
wp plugin activate kpi-dashboard

# Verify
wp kpi-dashboard info
```

**Expected Result:**
- All 16 tables created
- Default admin user created
- 6 default departments created
- No errors in debug log

**Test: Database Creation**
```bash
# Check all tables exist
wp db query "SHOW TABLES LIKE 'wp_kpi_%'"
```

**Expected Result:**
```
wp_kpi_users
wp_kpi_departments
wp_kpi_positions
... (16 tables total)
```

### 2. Authentication Testing

**Test: Login**
1. Go to `https://yoursite.com/kpi`
2. Enter username: `admin`
3. Enter password: `admin`
4. Click Login

**Expected Result:**
- Successful login
- Redirect to dashboard
- User menu shows admin name
- Token stored in localStorage

**Test: Logout**
1. Click user menu (top right)
2. Click Logout

**Expected Result:**
- Session cleared
- Redirect to login page
- Token removed from localStorage
- Cannot access protected routes

**Test: Invalid Credentials**
1. Try login with wrong password

**Expected Result:**
- Error message: "Invalid credentials"
- No redirect
- No token issued

**Test: Password Change**
1. Login
2. Go to Settings
3. Change password
4. Logout
5. Login with new password

**Expected Result:**
- Password change successful
- Can login with new password
- Cannot login with old password

### 3. User Management Testing

**Test: Create User**
```bash
wp kpi-dashboard create-user \
  --username=testuser \
  --email=test@example.com \
  --name="Test User" \
  --role=staff \
  --department=2 \
  --position=5
```

**Expected Result:**
- User created
- Email sent (welcome)
- User can login
- Assigned to correct department

**Test: Update User**
1. Go to Users
2. Edit a user
3. Change name, email, or role
4. Save

**Expected Result:**
- Changes saved
- User sees updated info
- Audit log created

**Test: Delete User**
1. Go to Users
2. Delete a user (soft delete)

**Expected Result:**
- User marked inactive
- Cannot login
- Data preserved
- Audit log created

### 4. Department Management Testing

**Test: Create Department**
1. Go to Departments
2. Click Add New
3. Fill: Name, Description
4. Save

**Expected Result:**
- Department created
- Shows in list
- Can assign users to it

**Test: Assign Department Head**
1. Go to Departments
2. Select a department
3. Assign a user as head

**Expected Result:**
- Head assigned
- User has dept_head permissions
- Shows as head in UI

### 5. KPI Testing

**Test: Create KPI**
1. Go to KPIs
2. Click Add New KPI
3. Fill all fields:
   - Name: "Test KPI"
   - Type: Department
   - Measurement: Number
   - Target: 100
   - Frequency: Monthly
4. Save

**Expected Result:**
- KPI created
- Shows in list
- Can be assigned

**Test: Assign KPI to Users**
1. Select a KPI
2. Click Assign
3. Select multiple users
4. Set individual targets
5. Save

**Expected Result:**
- KPI assigned to all selected users
- Each user sees their target
- Notifications sent

### 6. Data Entry Testing

**Test: Create Data Entry**
1. Login as staff user
2. Go to Data Entry
3. Select a KPI
4. Enter value
5. Add notes
6. Submit for approval

**Expected Result:**
- Entry created with status "pending"
- Notification sent to manager
- Shows in pending approvals
- User cannot edit after submit

**Test: Save as Draft**
1. Create data entry
2. Save as draft (don't submit)

**Expected Result:**
- Entry saved with status "draft"
- No notification sent
- User can edit later
- Not in approvals queue

**Test: Bulk Data Entry**
```bash
curl -X POST /wp-json/kpi/v1/data/bulk \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "entries": [
      {
        "kpi_id": 1,
        "period_start": "2025-01-01",
        "period_end": "2025-01-31",
        "value": 1000000
      },
      {
        "kpi_id": 2,
        "period_start": "2025-01-01",
        "period_end": "2025-01-31",
        "value": 15.5
      }
    ]
  }'
```

**Expected Result:**
```json
{
  "success": true,
  "data": {
    "created": 2,
    "failed": 0
  }
}
```

### 7. Approval Workflow Testing

**Test: Approve Data Entry**
1. Login as manager/dept head
2. Go to Approvals
3. Review pending entry
4. Click Approve
5. Add approval note
6. Submit

**Expected Result:**
- Entry status changed to "approved"
- Notification sent to submitter
- Entry counted in analytics
- Approval logged in audit trail

**Test: Reject Data Entry**
1. Go to Approvals
2. Select entry
3. Click Reject
4. Add reason
5. Submit

**Expected Result:**
- Entry status changed to "rejected"
- Notification with reason sent to submitter
- User can re-submit with corrections
- Rejection logged

**Test: Bulk Approve**
```bash
curl -X POST /wp-json/kpi/v1/approvals/bulk-approve \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "entry_ids": [20, 21, 22],
    "notes": "Batch approval"
  }'
```

**Expected Result:**
- All entries approved
- Bulk notifications sent
- Audit log entries created

### 8. Analytics Testing

**Test: Dashboard Overview**
1. Go to Analytics
2. View overview

**Expected Result:**
- Shows total KPIs, users, departments
- Displays pending approvals count
- Shows average achievement
- Lists top performers
- Trends chart visible

**Test: Department Comparison**
1. Go to Analytics → Departments
2. Select date range
3. View comparison

**Expected Result:**
- All departments listed
- Shows avg achievement per dept
- Visual chart/graph
- Sortable by performance

**Test: Trends Analysis**
1. Go to Analytics → Trends
2. Select KPI
3. Choose date range
4. View trends

**Expected Result:**
- Line chart showing performance over time
- Achievement percentages
- Above/below target indicators

### 9. Reports Testing

**Test: Generate Department Report**
```bash
curl -X POST /wp-json/kpi/v1/reports/department \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "department_id": 2,
    "period_start": "2025-01-01",
    "period_end": "2025-01-31",
    "format": "csv"
  }'
```

**Expected Result:**
```json
{
  "success": true,
  "data": {
    "report_id": 15,
    "download_url": "/wp-content/uploads/kpi-reports/dept-2-2025-01.csv",
    "expires_at": "2025-02-01 00:00:00"
  }
}
```

**Test: Download Report**
1. Generate report
2. Click download link
3. Open CSV file

**Expected Result:**
- Valid CSV file
- Contains all expected data
- Properly formatted
- Headers included

### 10. Notification Testing

**Test: In-App Notifications**
1. Trigger notification (e.g., KPI assignment)
2. Check bell icon
3. View notification

**Expected Result:**
- Notification appears
- Badge shows count
- Clicking opens notification
- Mark as read works

**Test: Email Notifications**
1. Trigger email notification
2. Check email inbox

**Expected Result:**
- Email received
- Proper formatting
- Contains relevant info
- Unsubscribe link works

---

## API Testing

### Using Postman

**Collection Setup:**
1. Import KPI Dashboard API collection
2. Set base URL: `https://yoursite.com/wp-json/kpi/v1`
3. Configure authentication

**Test: Login**
```http
POST /auth/login
Content-Type: application/json

{
  "username": "admin",
  "password": "admin"
}
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "username": "admin",
      "role": "super_admin"
    }
  }
}
```

**Test: Get Users**
```http
GET /users
Authorization: Bearer {access_token}
```

**Expected Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "username": "admin",
      "full_name": "Administrator"
    }
  ],
  "pagination": {
    "page": 1,
    "total": 1
  }
}
```

### Using curl

**Login Test:**
```bash
curl -X POST http://yoursite.com/wp-json/kpi/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin"}' \
  | jq
```

**Get Current User:**
```bash
TOKEN="your-access-token"

curl -X GET http://yoursite.com/wp-json/kpi/v1/auth/me \
  -H "Authorization: Bearer $TOKEN" \
  | jq
```

**Create KPI:**
```bash
curl -X POST http://yoursite.com/wp-json/kpi/v1/kpis \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test KPI",
    "type": "department",
    "measurement_type": "number",
    "target_value": 100,
    "department_id": 2
  }' | jq
```

---

## Database Testing

### Schema Validation

**Test: Check All Tables**
```sql
SHOW TABLES LIKE 'wp_kpi_%';
```

**Expected Result:** 16 tables

**Test: Check Indexes**
```sql
SHOW INDEX FROM wp_kpi_data;
```

**Expected Result:**
- Primary key on `id`
- Index on `kpi_id`
- Index on `user_id`
- Index on `status`
- Index on `period_start`, `period_end`

### Data Integrity

**Test: Foreign Key Constraints (Application Level)**
```sql
-- Try to insert data with invalid kpi_id
INSERT INTO wp_kpi_data (kpi_id, user_id, value)
VALUES (99999, 1, 100);
```

**Expected Result:**
- PHP application prevents this
- Validation error returned

**Test: Required Fields**
```sql
-- Try to insert without required fields
INSERT INTO wp_kpi_users (username) VALUES ('test');
```

**Expected Result:**
- Error: other required fields missing

### Performance Testing

**Test: Query Performance**
```sql
-- Analyze slow queries
EXPLAIN SELECT * FROM wp_kpi_data
WHERE user_id = 5
AND period_start >= '2025-01-01'
ORDER BY period_start DESC;
```

**Expected Result:**
- Uses index
- Rows scanned < 1000
- Type: ref or range

---

## Frontend Testing

### Browser Compatibility

Test in:
- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+

### Responsive Design

Test at breakpoints:
- ✅ Mobile (375px)
- ✅ Tablet (768px)
- ✅ Desktop (1024px)
- ✅ Wide (1920px)

### User Interface

**Test: Navigation**
1. Click all menu items
2. Verify correct page loads
3. Check breadcrumbs

**Expected Result:**
- All links work
- No 404 errors
- Smooth transitions

**Test: Forms**
1. Fill out all forms
2. Test validation
3. Submit

**Expected Result:**
- Validation messages show
- Required fields enforced
- Success/error messages clear

**Test: Dark Mode**
1. Toggle dark mode
2. Check all pages

**Expected Result:**
- All pages render correctly
- No contrast issues
- Preference persisted

---

## Performance Testing

### Load Time

**Test: Initial Page Load**
```bash
curl -w "@curl-format.txt" -o /dev/null -s https://yoursite.com/kpi
```

**Expected Result:**
- Total time < 2 seconds
- Time to first byte < 500ms

### API Performance

**Test: API Response Time**
```bash
ab -n 100 -c 10 -H "Authorization: Bearer TOKEN" \
  http://yoursite.com/wp-json/kpi/v1/users
```

**Expected Result:**
- Mean response time < 200ms
- No failed requests
- Requests per second > 50

### Database Performance

**Test: Query Speed**
```bash
wp db query "SELECT COUNT(*) FROM wp_kpi_data WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 MONTH)" --debug
```

**Expected Result:**
- Query time < 100ms
- Uses indexes

---

## Security Testing

### Authentication

**Test: Unauthorized Access**
```bash
curl http://yoursite.com/wp-json/kpi/v1/users
```

**Expected Result:**
```json
{
  "success": false,
  "code": "not_authenticated",
  "message": "Authentication required"
}
```

**Test: Invalid Token**
```bash
curl -H "Authorization: Bearer invalid-token" \
  http://yoursite.com/wp-json/kpi/v1/users
```

**Expected Result:**
```json
{
  "success": false,
  "code": "invalid_token"
}
```

### SQL Injection

**Test: SQL Injection in Login**
```bash
curl -X POST http://yoursite.com/wp-json/kpi/v1/auth/login \
  -d '{"username":"admin' OR '1'='1","password":"anything"}'
```

**Expected Result:**
- Login fails
- No SQL error
- Request safely handled

### XSS Testing

**Test: Script Injection**
1. Try to create user with name: `<script>alert('XSS')</script>`

**Expected Result:**
- Script tags sanitized
- No script execution
- Safe display in UI

---

## Test Scenarios

### Scenario 1: New Employee Onboarding

**Steps:**
1. Admin creates new user account
2. User receives welcome email
3. User logs in, changes password
4. User sees assigned KPIs
5. User enters first data entry

**Verification:**
- [ ] User created successfully
- [ ] Welcome email received
- [ ] Can login with credentials
- [ ] Password change works
- [ ] KPIs visible
- [ ] Can submit data

### Scenario 2: Monthly Review Process

**Steps:**
1. All staff submit monthly data
2. Managers review submissions
3. Managers approve/reject
4. Department heads view analytics
5. Super admin generates reports

**Verification:**
- [ ] All data submitted
- [ ] Notifications sent
- [ ] Approvals processed
- [ ] Analytics accurate
- [ ] Reports generated

### Scenario 3: Performance Review

**Steps:**
1. Manager accesses analytics
2. Views top performers
3. Identifies low performers
4. Generates performance report
5. Exports data for HR

**Verification:**
- [ ] Analytics load correctly
- [ ] Rankings accurate
- [ ] Report contains correct data
- [ ] Export successful
- [ ] Data format correct

---

## Regression Testing

After each update, test:

- [ ] Login/logout
- [ ] User CRUD operations
- [ ] KPI creation and assignment
- [ ] Data entry workflow
- [ ] Approval process
- [ ] Analytics calculations
- [ ] Report generation
- [ ] Notifications
- [ ] API endpoints
- [ ] Database integrity

---

## Test Data

### Sample Users
```
admin / admin - Super Admin
john.doe / password123 - Super Admin
sarah.sales / password123 - Dept Head (Sales)
mike.marketing / password123 - Dept Head (Marketing)
```

### Sample KPIs
```
ID 1: Monthly Sales Revenue (Department - Sales)
ID 2: Conversion Rate (Department - Marketing)
ID 3: Customer Satisfaction (Position - Sales Executive)
```

### Sample Data Entries
Generate via:
```bash
wp kpi-dashboard generate-data
```

---

## Continuous Testing

### Automated Testing (Future)

**Unit Tests (PHPUnit):**
```bash
phpunit tests/
```

**Frontend Tests (Jest):**
```bash
npm test
```

**E2E Tests (Cypress):**
```bash
npx cypress run
```

### CI/CD Integration

Tests run automatically on:
- Every commit (GitHub Actions)
- Pull requests
- Before deployment

---

## Test Reports

Document results:
```
Date: 2025-01-06
Tester: Your Name
Version: 1.0.0

Tests Run: 50
Passed: 48
Failed: 2
Skipped: 0

Issues Found:
1. Minor UI issue on mobile
2. Slow query on large datasets

Overall: PASS
```

---

## Best Practices

1. **Test Early:** Start testing during development
2. **Test Often:** Run regression tests after changes
3. **Automate:** Use automated tests where possible
4. **Document:** Record test results
5. **Fix Fast:** Address issues immediately
6. **Retest:** Verify fixes work

---

For more testing guidance, see:
- [Installation Guide](docs/INSTALLATION.md)
- [API Documentation](docs/API.md)
- [Deployment Guide](docs/DEPLOYMENT.md)
