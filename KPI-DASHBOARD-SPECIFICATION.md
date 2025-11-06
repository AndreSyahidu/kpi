# KPI Dashboard WordPress Plugin - Complete Specification

## 📋 Project Overview

**Target**: Standalone KPI Management System untuk perusahaan 100 karyawan
**Access**: domain.com/kpi (independent login, tidak via wp-admin)
**Data Retention**: 3 tahun historical data
**Design**: Modern Minimalist Corporate with Dark Mode support

---

## 🎯 Core Features

### 1. **Standalone Access System**
- Custom login page at `/kpi`
- Independent dari WordPress default login
- Session management terpisah
- Remember me functionality
- Password reset via email
- Two-factor authentication (optional, future)

### 2. **User Management System**

#### **Role Hierarchy**
```
Super Admin KPI
├── Full access to everything
├── Manage all users
├── Global settings
└── System configuration

Department Head
├── Full access to assigned department(s)
├── Manage users in department
├── Approve/reject data submissions
└── Department settings

Manager
├── View & input data for department
├── View team member data
├── Submit data for approval
└── Personal dashboard

Staff
├── View only for assigned department
├── View personal KPIs
└── Limited dashboard access
```

#### **Permission Matrix**
| Feature | Super Admin | Dept Head | Manager | Staff |
|---------|------------|-----------|---------|-------|
| User Management (All) | ✅ | ❌ | ❌ | ❌ |
| User Management (Dept) | ✅ | ✅ | ❌ | ❌ |
| Department CRUD | ✅ | View Only | View Only | View Only |
| KPI Definition CRUD | ✅ | ✅ (Own Dept) | View Only | View Only |
| Data Input | ✅ | ✅ | ✅ | ❌ |
| Data Approval | ✅ | ✅ | ❌ | ❌ |
| Data Edit (Own) | ✅ | ✅ | ✅ (Pending) | ❌ |
| Data Edit (Historical) | ✅ | ✅ (With reason) | ❌ | ❌ |
| Data Delete | ✅ | ❌ | ❌ | ❌ |
| Export Reports | ✅ | ✅ (Own Dept) | ✅ (Own Dept) | ❌ |
| View All Departments | ✅ | ❌ | ❌ | ❌ |
| System Settings | ✅ | ❌ | ❌ | ❌ |
| Audit Logs | ✅ | ✅ (Own Dept) | ❌ | ❌ |

### 3. **Department & Position Management**

#### **Department Structure**
```
Department
├── ID, Name, Description
├── Color code (for UI differentiation)
├── Icon/Logo
├── Parent Department (for hierarchy)
├── Department Head(s) assigned
└── Active/Inactive status
```

#### **Position/Role within Department**
```
Position
├── Title (e.g., "Sales Executive", "Marketing Specialist")
├── Department
├── Level (Junior, Senior, Lead, Manager)
├── KPIs assigned to this position
└── Users assigned to this position
```

**Example Structure**:
```
Sales Department
├── Sales Director (Dept Head)
├── Sales Manager
│   ├── Senior Sales Executive
│   └── Sales Executive
└── Sales Support

Marketing Department
├── Marketing Director (Dept Head)
├── Content Marketing Manager
├── Digital Marketing Manager
│   └── Social Media Specialist
└── SEO Specialist
```

### 4. **KPI Management System**

#### **KPI Types**
1. **Department KPI**: Track at department level
2. **Position KPI**: Track per position/role
3. **Personal KPI**: Individual targets

#### **KPI Definition**
```
KPI
├── Name (e.g., "Monthly Sales Revenue")
├── Description
├── Category (Sales, Marketing, Operations, HR, Finance, Custom)
├── Type (Department/Position/Personal)
├── Assigned to: Department(s) or Position(s)
├── Metric Type:
│   ├── Number (with unit: IDR, USD, units, etc.)
│   ├── Percentage (%)
│   ├── Ratio (x/y format)
│   ├── Custom unit
│   └── Boolean (Yes/No, Pass/Fail)
├── Input Frequency (Daily/Weekly/Monthly/Quarterly/Yearly)
├── Target/Goal settings:
│   ├── Target value
│   ├── Target type (minimum/maximum/exact/range)
│   └── Target period
├── Calculation method:
│   ├── Sum (total)
│   ├── Average
│   ├── Count
│   ├── Custom formula
│   └── Auto-calculated from other KPIs
├── Weight/Priority (for scoring)
├── Display settings:
│   ├── Chart type (line/bar/pie/area/gauge)
│   ├── Color scheme
│   └── Decimal places
└── Active/Inactive status
```

#### **KPI Categories (Customizable)**
Default categories:
- Sales & Revenue
- Marketing & Leads
- Customer Service
- Operations & Production
- Human Resources
- Finance & Accounting
- IT & Technology
- Custom Categories (user-defined)

### 5. **Data Entry & Workflow**

#### **Input Process**
```
Weekly Reminder (Automated)
    ↓
Manager/Dept Head Input Data
    ↓
Data Validation (format, range checks)
    ↓
Submit for Review
    ↓
Department Head Review
    ├── Approve → Data goes live
    ├── Reject → Back to Manager with notes
    └── Request Changes → Manager edits
    ↓
Data Appears in Dashboard
    ↓
Audit Log Created
```

#### **Data Entry Form Features**
- Bulk entry for multiple KPIs
- Previous period data reference
- Auto-save draft
- File attachment support (evidence/documentation)
- Notes/Comments field
- Validation rules per KPI
- Copy from previous period
- Quick entry mode vs detailed mode

#### **Edit & Delete Permissions (Recommended)**
```
Edit Own Pending Data: Manager (unlimited)
Edit Approved Data (Current Period): Dept Head (with reason + audit)
Edit Historical Data (>1 month): Super Admin only (with reason + approval)
Delete Pending Data: Manager + Dept Head
Delete Approved Data: Super Admin only (with reason + audit)
Soft Delete: All data (recoverable for 30 days)
```

**Audit Trail Required For**:
- All edits to approved data
- All deletions
- User/role changes
- Department/KPI definition changes
- Settings changes

### 6. **Dashboard & Visualization**

#### **Main Dashboard Tabs**
```
1. 📊 Overview Dashboard
   ├── Company-wide KPI summary
   ├── Current period vs target
   ├── Trend charts (last 6 months)
   ├── Quick stats cards
   └── Recent activities

2. 🏢 Departments
   ├── Department list with KPI summary
   ├── Department comparison
   ├── Department details view
   └── Department management (Admin)

3. 🎯 KPI Management
   ├── All KPIs list/grid view
   ├── Create/Edit KPI definitions
   ├── KPI categories
   ├── KPI assignment to departments/positions
   └── KPI templates

4. 📝 Data Entry
   ├── Pending submissions
   ├── Entry form
   ├── Batch entry
   ├── Upload from Excel/CSV
   └── Entry history

5. ✅ Approvals (Dept Head & Admin)
   ├── Pending approvals queue
   ├── Quick approve/reject
   ├── Review details with history
   └── Bulk approval

6. 📈 Reports & Analytics
   ├── Pre-built report templates
   ├── Custom report builder
   ├── Export to PDF/Excel/CSV
   ├── Schedule automated reports
   └── Report history

7. 🏆 Insights & Performance
   ├── Best performing departments
   ├── Low performing areas (needs attention)
   ├── Top performers (individuals)
   ├── Trend analysis
   ├── Predictive insights
   └── Benchmark comparison

8. 👥 User Management
   ├── User list (filterable)
   ├── Add/Edit/Deactivate users
   ├── Role assignment
   ├── Bulk import users
   └── User activity logs

9. ⚙️ Settings
   ├── General settings
   ├── Notification settings
   ├── Email templates
   ├── Branding (logo, colors)
   ├── Data retention policies
   └── System logs
```

#### **Dashboard Views**

**1. Company Overview**
- Total KPIs tracked
- Overall performance score
- Trending up/down indicators
- Department performance matrix
- Top 5 best performing KPIs
- Top 5 underperforming KPIs
- Timeline selector (This Week/Month/Quarter/Year)

**2. Department View**
- Department selector dropdown
- Department KPI cards
- Comparison with targets
- Historical trend charts
- Team performance (if has sub-positions)
- Detailed metrics table

**3. Personal View**
- User's personal KPIs
- Position-based KPIs
- Department context
- Personal performance score
- Goals vs achievements
- Activity timeline

**4. Comparison View**
- Multi-department comparison
- Side-by-side charts
- Performance ranking
- Best practices sharing
- Peer benchmarking

**5. Insights Dashboard** (NEW)
```
Best Performance Section
├── Top 3 departments (current period)
├── Most improved department (vs last period)
├── Consistently high performers (3+ periods)
└── Achievement highlights

Low Performance Section
├── Departments below 70% target
├── Declining trends (2+ consecutive periods)
├── Critical KPIs needing attention
└── Recommended actions

Trend Analysis
├── Seasonality detection
├── Growth trajectory
├── Anomaly detection
└── Forecast projections

Individual Recognition
├── Top performers (individuals)
├── Most improved
├── Consistent achievers
└── Milestone achievements
```

#### **Chart Types Available**
- Line charts (trends over time)
- Bar charts (comparisons)
- Pie/Donut charts (composition)
- Area charts (volume trends)
- Gauge charts (current vs target)
- Heatmaps (multi-dimensional data)
- Radar charts (multi-metric comparison)
- Waterfall charts (cumulative effects)

### 7. **Comparison & Time Periods**

#### **Time Period Selectors**
- Today / This Week / This Month / This Quarter / This Year
- Last Week / Last Month / Last Quarter / Last Year
- Custom date range
- Rolling periods (Last 7/30/90/180/365 days)

#### **Comparison Features**
```
Period Comparison
├── Current vs Previous period
├── Current vs Same period last year (YoY)
├── Month over Month (MoM)
├── Quarter over Quarter (QoQ)
└── Year over Year (YoY)

Department Comparison
├── All departments side-by-side
├── Selected departments
├── Department vs Company average
└── Department ranking

Position Comparison
├── Same position across departments
├── Different positions in same department
└── Individual vs Position average

Target Comparison
├── Actual vs Target
├── Achievement percentage
├── Gap analysis
└── Forecast vs Target
```

### 8. **Notifications & Alerts System**

#### **Notification Channels**
- In-app notifications (bell icon)
- Email notifications
- Browser push notifications (optional)
- Daily/Weekly digest emails

#### **Notification Triggers (Customizable)**

**Default Recommended Triggers**:
```
Data Entry Reminders
├── Due date approaching (2 days before)
├── Overdue data entry
└── Weekly reminder on Monday morning

Approval Needed
├── New submission awaiting approval
├── Pending approvals reminder (daily)
└── Approval deadline approaching

Performance Alerts
├── KPI below 70% of target (Warning)
├── KPI below 50% of target (Critical)
├── KPI achieved 100%+ (Success)
├── KPI above 120% (Outstanding)
└── Significant drop (>20% vs last period)

Trend Alerts
├── 2 consecutive periods below target
├── 3 consecutive periods declining
└── New record high achieved

System Notifications
├── User account created
├── Role changed
├── Password reset requested
├── Data export ready
└── System maintenance scheduled
```

#### **Alert Configuration**
```
Per User Preferences
├── Enable/Disable notification types
├── Notification frequency
├── Quiet hours
├── Email digest preference
└── Channels (in-app/email/push)

Per KPI Alert Rules
├── Threshold values (custom per KPI)
├── Alert severity (info/warning/critical)
├── Recipient rules (who gets notified)
├── Escalation rules (if not addressed)
└── Snooze options
```

### 9. **Reports & Export**

#### **Pre-built Report Templates**
1. **Executive Summary** (Company-wide overview)
2. **Department Performance Report**
3. **KPI Detailed Report** (Single KPI deep dive)
4. **Comparison Report** (Departments/Periods)
5. **Trend Analysis Report**
6. **Individual Performance Report**
7. **Custom Report** (User-defined)

#### **Report Customization**
- Select date range
- Filter by departments
- Filter by KPI categories
- Include/exclude specific KPIs
- Chart selection
- Table/Grid options
- Branding (logo, colors, company info)

#### **Export Formats**
- **PDF**: Formatted, print-ready reports
- **Excel (.xlsx)**: Editable, with charts and raw data
- **CSV**: Raw data for external analysis
- **PowerPoint (.pptx)**: Presentation-ready (future)
- **JSON**: API export for integrations (future)

#### **Scheduled Reports (Automated)**
- Weekly/Monthly/Quarterly reports
- Email to specified recipients
- Auto-generate on specific date
- Custom report templates
- Archive in system

### 10. **Additional Features**

#### **Audit Trail & Activity Logs**
```
Track Everything:
├── User login/logout
├── Data entry/edit/delete
├── Approval actions
├── User changes
├── Settings changes
├── Export actions
└── Failed login attempts

Log Details:
├── Timestamp
├── User who performed action
├── Action type
├── Before/After values (for edits)
├── IP address
├── Reason (for edits/deletes)
└── Status (success/failed)
```

#### **Data Validation & Quality**
- Min/Max value validation
- Format validation (number/percentage)
- Required fields enforcement
- Duplicate entry detection
- Anomaly detection (unusual values)
- Cross-KPI validation (formulas)

#### **Comments & Collaboration**
- Comments on data entries
- @mentions to notify users
- Threaded discussions
- File attachments
- Status tracking (Open/Resolved)

#### **Search & Filters**
- Global search (users/departments/KPIs/data)
- Advanced filters on all listing pages
- Saved filter sets
- Quick filters (predefined)
- Sort by multiple columns

#### **Responsive & Mobile**
- Fully responsive design (mobile/tablet/desktop)
- Touch-friendly interface
- Mobile-optimized charts
- Progressive Web App (PWA) capable
- Offline viewing (cached data)

#### **Dark Mode**
- System preference detection
- Manual toggle
- Persistent per user
- Optimized chart colors for dark mode

---

## 🏗️ Technical Architecture

### **Technology Stack**

#### **Backend**
```
WordPress: 6.4+ (Latest stable)
PHP: 8.1+ (Modern PHP features)
Database: MySQL 8.0+ / MariaDB 10.6+
```

#### **Frontend**
```
Framework: React 18+ with TypeScript
Build Tool: Vite (Fast, modern)
State Management: Zustand (Lightweight, simple)
Routing: React Router v6
UI Framework: Material-UI (MUI) v5
    ├── Modern, corporate look
    ├── Extensive component library
    ├── Built-in dark mode
    └── Great TypeScript support
Charts: ApexCharts
    ├── Beautiful, interactive charts
    ├── Responsive out of the box
    ├── Many chart types
    └── Good documentation
Form Handling: React Hook Form + Zod validation
Date Handling: date-fns (lightweight)
HTTP Client: Axios with interceptors
Icons: Material Icons + Lucide React
Notifications: Notistack (snackbar notifications)
Tables: TanStack Table (React Table v8)
    ├── Powerful data grids
    ├── Sorting, filtering, pagination
    └── Virtual scrolling for large datasets
```

#### **API Layer**
```
WordPress REST API (Custom endpoints)
├── /wp-json/kpi/v1/auth/* (Authentication)
├── /wp-json/kpi/v1/users/* (User management)
├── /wp-json/kpi/v1/departments/* (Departments)
├── /wp-json/kpi/v1/kpis/* (KPI definitions)
├── /wp-json/kpi/v1/data/* (KPI data entries)
├── /wp-json/kpi/v1/approvals/* (Approval workflow)
├── /wp-json/kpi/v1/reports/* (Reports & exports)
├── /wp-json/kpi/v1/notifications/* (Notifications)
├── /wp-json/kpi/v1/analytics/* (Insights & analytics)
└── /wp-json/kpi/v1/settings/* (System settings)

JWT Tokens for authentication
Rate limiting for security
Request/Response caching
API versioning for future updates
```

#### **Database Schema (Custom Tables)**

```sql
-- Users (extends WP users or standalone)
wp_kpi_users
├── id (PK)
├── wp_user_id (FK, nullable - can be null for non-WP users)
├── username (unique)
├── email (unique)
├── password_hash
├── full_name
├── avatar_url
├── role (super_admin/dept_head/manager/staff)
├── department_id (FK)
├── position_id (FK, nullable)
├── is_active
├── last_login
├── created_at
├── updated_at
└── created_by (FK)

-- Departments
wp_kpi_departments
├── id (PK)
├── name
├── slug (unique)
├── description
├── parent_id (FK, self-reference for hierarchy)
├── color_code (#hex)
├── icon_class
├── logo_url
├── sort_order
├── is_active
├── created_at
├── updated_at
└── created_by (FK)

-- Positions/Roles within departments
wp_kpi_positions
├── id (PK)
├── title
├── department_id (FK)
├── level (junior/senior/lead/manager)
├── description
├── sort_order
├── is_active
├── created_at
├── updated_at
└── created_by (FK)

-- Department Heads assignment
wp_kpi_department_heads
├── id (PK)
├── department_id (FK)
├── user_id (FK)
├── assigned_at
└── assigned_by (FK)

-- KPI Definitions
wp_kpi_definitions
├── id (PK)
├── name
├── slug (unique)
├── description
├── category (sales/marketing/operations/hr/finance/custom)
├── type (department/position/personal)
├── metric_type (number/percentage/ratio/custom/boolean)
├── unit (IDR/USD/units/custom)
├── input_frequency (daily/weekly/monthly/quarterly/yearly)
├── calculation_method (sum/average/count/formula/auto)
├── formula (JSON, for calculated KPIs)
├── target_value (decimal)
├── target_type (minimum/maximum/exact/range)
├── target_range_min (decimal, nullable)
├── target_range_max (decimal, nullable)
├── weight (int, for scoring)
├── chart_type (line/bar/pie/area/gauge)
├── color_scheme (JSON)
├── decimal_places (int)
├── validation_rules (JSON)
├── is_active
├── created_at
├── updated_at
└── created_by (FK)

-- KPI Assignment to Departments/Positions
wp_kpi_assignments
├── id (PK)
├── kpi_id (FK)
├── assigned_to_type (department/position/user)
├── assigned_to_id (department_id/position_id/user_id)
├── target_override (decimal, nullable - custom target)
├── is_active
├── assigned_at
└── assigned_by (FK)

-- KPI Data Entries
wp_kpi_data
├── id (PK)
├── kpi_id (FK)
├── department_id (FK)
├── position_id (FK, nullable)
├── user_id (FK, nullable - if personal KPI)
├── period_start (date)
├── period_end (date)
├── value (decimal)
├── unit
├── status (draft/pending/approved/rejected)
├── notes (text)
├── attachments (JSON array of file URLs)
├── submitted_by (FK)
├── submitted_at
├── reviewed_by (FK, nullable)
├── reviewed_at (nullable)
├── review_notes (text, nullable)
├── created_at
├── updated_at
└── is_deleted (soft delete)

-- Audit Trail
wp_kpi_audit_logs
├── id (PK)
├── user_id (FK)
├── action (login/create/edit/delete/approve/reject/export)
├── entity_type (user/department/kpi/data/setting)
├── entity_id
├── before_value (JSON, nullable)
├── after_value (JSON, nullable)
├── reason (text, nullable - for edits/deletes)
├── ip_address
├── user_agent
├── created_at
└── INDEX (entity_type, entity_id)

-- Notifications
wp_kpi_notifications
├── id (PK)
├── user_id (FK)
├── type (reminder/approval/alert/info)
├── severity (info/warning/critical/success)
├── title
├── message
├── action_url (nullable)
├── related_entity_type (nullable)
├── related_entity_id (nullable)
├── is_read
├── read_at (nullable)
├── sent_via_email (boolean)
├── email_sent_at (nullable)
├── created_at
└── INDEX (user_id, is_read)

-- Alert Rules
wp_kpi_alert_rules
├── id (PK)
├── name
├── kpi_id (FK, nullable - null for global rules)
├── condition_type (below_target/above_target/declining_trend/no_data)
├── threshold_value (decimal)
├── threshold_operator (</>/=/<=/>=/between)
├── consecutive_periods (int, for trend rules)
├── severity (warning/critical)
├── recipients (JSON array of user_ids/roles)
├── notification_channels (JSON: email/in_app/push)
├── is_active
├── created_at
├── updated_at
└── created_by (FK)

-- User Notification Preferences
wp_kpi_user_notification_prefs
├── id (PK)
├── user_id (FK)
├── notification_type (reminder/approval/alert/info)
├── enabled (boolean)
├── channel (email/in_app/push)
├── frequency (realtime/hourly/daily/weekly)
├── quiet_hours_start (time, nullable)
├── quiet_hours_end (time, nullable)
└── updated_at

-- Scheduled Reports
wp_kpi_scheduled_reports
├── id (PK)
├── name
├── template_type (executive/department/kpi/comparison/trend)
├── config (JSON - filters, options)
├── frequency (weekly/monthly/quarterly)
├── schedule_day (int - day of week/month)
├── recipients (JSON array of emails)
├── format (pdf/excel/csv)
├── is_active
├── last_run_at (nullable)
├── next_run_at
├── created_at
├── created_by (FK)
└── updated_at

-- Report History
wp_kpi_report_history
├── id (PK)
├── scheduled_report_id (FK, nullable)
├── generated_by (FK)
├── report_type
├── config (JSON)
├── file_path
├── file_size
├── generated_at
└── INDEX (generated_at)

-- Comments/Collaboration
wp_kpi_comments
├── id (PK)
├── parent_id (FK, self-reference for threading)
├── entity_type (data_entry/kpi_definition)
├── entity_id
├── user_id (FK)
├── comment_text
├── mentions (JSON array of user_ids)
├── attachments (JSON)
├── status (open/resolved)
├── created_at
├── updated_at
└── is_deleted

-- Settings/Config
wp_kpi_settings
├── id (PK)
├── setting_key (unique)
├── setting_value (longtext, JSON)
├── setting_type (general/notification/email/branding/system)
├── updated_at
└── updated_by (FK)

-- Sessions (for custom authentication)
wp_kpi_sessions
├── id (PK)
├── user_id (FK)
├── token_hash (unique)
├── ip_address
├── user_agent
├── expires_at
├── created_at
└── INDEX (token_hash, expires_at)
```

**Database Indexes for Performance**:
```sql
-- Critical indexes
INDEX idx_kpi_data_period ON wp_kpi_data(period_start, period_end)
INDEX idx_kpi_data_status ON wp_kpi_data(status)
INDEX idx_kpi_data_composite ON wp_kpi_data(kpi_id, department_id, period_start)
INDEX idx_notifications_unread ON wp_kpi_notifications(user_id, is_read, created_at)
INDEX idx_audit_logs_entity ON wp_kpi_audit_logs(entity_type, entity_id, created_at)
INDEX idx_users_active ON wp_kpi_users(is_active, department_id)
```

### **File Structure**

```
kpi-dashboard-plugin/
│
├── kpi-dashboard.php                 # Main plugin file
├── readme.txt                         # WordPress plugin readme
├── package.json                       # Node dependencies
├── tsconfig.json                      # TypeScript config
├── vite.config.ts                     # Vite build config
├── composer.json                      # PHP dependencies
│
├── includes/                          # PHP backend
│   ├── class-kpi-dashboard.php       # Main plugin class
│   ├── class-activator.php           # Plugin activation
│   ├── class-deactivator.php         # Plugin deactivation
│   ├── class-loader.php              # Hooks loader
│   │
│   ├── core/                          # Core functionality
│   │   ├── class-router.php          # Custom routing for /kpi
│   │   ├── class-auth.php            # Authentication system
│   │   ├── class-session.php         # Session management
│   │   └── class-permissions.php     # Permission checking
│   │
│   ├── api/                           # REST API endpoints
│   │   ├── class-api-base.php        # Base API class
│   │   ├── class-auth-api.php        # Auth endpoints
│   │   ├── class-users-api.php       # Users CRUD
│   │   ├── class-departments-api.php # Departments CRUD
│   │   ├── class-positions-api.php   # Positions CRUD
│   │   ├── class-kpis-api.php        # KPI definitions CRUD
│   │   ├── class-data-api.php        # KPI data entries
│   │   ├── class-approvals-api.php   # Approval workflow
│   │   ├── class-reports-api.php     # Reports & exports
│   │   ├── class-analytics-api.php   # Insights & analytics
│   │   ├── class-notifications-api.php # Notifications
│   │   └── class-settings-api.php    # Settings
│   │
│   ├── models/                        # Data models
│   │   ├── class-user.php
│   │   ├── class-department.php
│   │   ├── class-position.php
│   │   ├── class-kpi.php
│   │   ├── class-kpi-data.php
│   │   ├── class-notification.php
│   │   └── class-audit-log.php
│   │
│   ├── services/                      # Business logic
│   │   ├── class-user-service.php
│   │   ├── class-department-service.php
│   │   ├── class-kpi-service.php
│   │   ├── class-data-service.php
│   │   ├── class-approval-service.php
│   │   ├── class-notification-service.php
│   │   ├── class-email-service.php
│   │   ├── class-report-generator.php
│   │   ├── class-analytics-service.php
│   │   └── class-validation-service.php
│   │
│   ├── database/                      # Database management
│   │   ├── class-db-schema.php       # Schema definitions
│   │   ├── class-migrations.php      # Migration runner
│   │   └── migrations/               # Migration files
│   │       ├── 001-create-users-table.php
│   │       ├── 002-create-departments-table.php
│   │       └── ...
│   │
│   ├── admin/                         # WP Admin integration (minimal)
│   │   ├── class-admin-menu.php      # Admin menu items
│   │   └── class-admin-settings.php  # Basic WP settings page
│   │
│   └── utils/                         # Utility classes
│       ├── class-validator.php       # Data validation
│       ├── class-sanitizer.php       # Input sanitization
│       ├── class-logger.php          # Error logging
│       ├── class-cache.php           # Caching helper
│       └── class-export.php          # Export utilities
│
├── assets/                            # Frontend React app
│   ├── src/
│   │   ├── main.tsx                  # App entry point
│   │   ├── App.tsx                   # Root component
│   │   ├── vite-env.d.ts            # Vite types
│   │   │
│   │   ├── config/                   # Configuration
│   │   │   ├── api.config.ts        # API endpoints
│   │   │   ├── theme.config.ts      # MUI theme
│   │   │   └── constants.ts         # App constants
│   │   │
│   │   ├── types/                    # TypeScript types
│   │   │   ├── user.types.ts
│   │   │   ├── department.types.ts
│   │   │   ├── kpi.types.ts
│   │   │   ├── data.types.ts
│   │   │   ├── notification.types.ts
│   │   │   └── index.ts
│   │   │
│   │   ├── store/                    # Zustand state management
│   │   │   ├── index.ts             # Combined store
│   │   │   ├── authStore.ts         # Auth state
│   │   │   ├── userStore.ts         # Users state
│   │   │   ├── departmentStore.ts   # Departments state
│   │   │   ├── kpiStore.ts          # KPIs state
│   │   │   ├── dataStore.ts         # KPI data state
│   │   │   ├── notificationStore.ts # Notifications state
│   │   │   └── settingsStore.ts     # Settings state
│   │   │
│   │   ├── services/                 # API service layer
│   │   │   ├── api.service.ts       # Base API client
│   │   │   ├── auth.service.ts
│   │   │   ├── user.service.ts
│   │   │   ├── department.service.ts
│   │   │   ├── kpi.service.ts
│   │   │   ├── data.service.ts
│   │   │   ├── report.service.ts
│   │   │   └── notification.service.ts
│   │   │
│   │   ├── routes/                   # React Router
│   │   │   ├── index.tsx            # Route definitions
│   │   │   ├── PrivateRoute.tsx     # Auth guard
│   │   │   └── RoleRoute.tsx        # Role-based guard
│   │   │
│   │   ├── layouts/                  # Layout components
│   │   │   ├── AuthLayout.tsx       # Login page layout
│   │   │   ├── DashboardLayout.tsx  # Main app layout
│   │   │   ├── Header.tsx           # Top navigation
│   │   │   ├── Sidebar.tsx          # Side navigation
│   │   │   └── Footer.tsx
│   │   │
│   │   ├── pages/                    # Page components
│   │   │   ├── auth/
│   │   │   │   ├── LoginPage.tsx
│   │   │   │   ├── ForgotPasswordPage.tsx
│   │   │   │   └── ResetPasswordPage.tsx
│   │   │   │
│   │   │   ├── dashboard/
│   │   │   │   ├── OverviewPage.tsx
│   │   │   │   ├── CompanyDashboard.tsx
│   │   │   │   ├── DepartmentDashboard.tsx
│   │   │   │   ├── PersonalDashboard.tsx
│   │   │   │   └── ComparisonDashboard.tsx
│   │   │   │
│   │   │   ├── departments/
│   │   │   │   ├── DepartmentsPage.tsx
│   │   │   │   ├── DepartmentListView.tsx
│   │   │   │   ├── DepartmentDetailView.tsx
│   │   │   │   └── DepartmentFormDialog.tsx
│   │   │   │
│   │   │   ├── kpis/
│   │   │   │   ├── KPIManagementPage.tsx
│   │   │   │   ├── KPIListView.tsx
│   │   │   │   ├── KPIDetailView.tsx
│   │   │   │   ├── KPIFormDialog.tsx
│   │   │   │   └── KPICategoriesView.tsx
│   │   │   │
│   │   │   ├── data-entry/
│   │   │   │   ├── DataEntryPage.tsx
│   │   │   │   ├── DataEntryForm.tsx
│   │   │   │   ├── BulkEntryForm.tsx
│   │   │   │   ├── UploadDataDialog.tsx
│   │   │   │   └── EntryHistoryView.tsx
│   │   │   │
│   │   │   ├── approvals/
│   │   │   │   ├── ApprovalsPage.tsx
│   │   │   │   ├── PendingApprovalsQueue.tsx
│   │   │   │   ├── ApprovalDetailDialog.tsx
│   │   │   │   └── BulkApprovalDialog.tsx
│   │   │   │
│   │   │   ├── reports/
│   │   │   │   ├── ReportsPage.tsx
│   │   │   │   ├── ReportTemplates.tsx
│   │   │   │   ├── CustomReportBuilder.tsx
│   │   │   │   ├── ScheduledReports.tsx
│   │   │   │   └── ReportHistory.tsx
│   │   │   │
│   │   │   ├── insights/
│   │   │   │   ├── InsightsPage.tsx
│   │   │   │   ├── BestPerformanceView.tsx
│   │   │   │   ├── LowPerformanceView.tsx
│   │   │   │   ├── TrendAnalysisView.tsx
│   │   │   │   └── PredictiveInsightsView.tsx
│   │   │   │
│   │   │   ├── users/
│   │   │   │   ├── UsersPage.tsx
│   │   │   │   ├── UserListView.tsx
│   │   │   │   ├── UserFormDialog.tsx
│   │   │   │   ├── BulkImportDialog.tsx
│   │   │   │   └── UserActivityLogs.tsx
│   │   │   │
│   │   │   ├── settings/
│   │   │   │   ├── SettingsPage.tsx
│   │   │   │   ├── GeneralSettings.tsx
│   │   │   │   ├── NotificationSettings.tsx
│   │   │   │   ├── EmailSettings.tsx
│   │   │   │   ├── BrandingSettings.tsx
│   │   │   │   └── SystemSettings.tsx
│   │   │   │
│   │   │   └── NotFoundPage.tsx
│   │   │
│   │   ├── components/               # Reusable components
│   │   │   ├── common/
│   │   │   │   ├── Button.tsx
│   │   │   │   ├── Card.tsx
│   │   │   │   ├── DataTable.tsx
│   │   │   │   ├── Dialog.tsx
│   │   │   │   ├── Form/
│   │   │   │   │   ├── FormInput.tsx
│   │   │   │   │   ├── FormSelect.tsx
│   │   │   │   │   ├── FormDatePicker.tsx
│   │   │   │   │   └── FormFileUpload.tsx
│   │   │   │   ├── Loading.tsx
│   │   │   │   ├── EmptyState.tsx
│   │   │   │   ├── ErrorBoundary.tsx
│   │   │   │   └── Pagination.tsx
│   │   │   │
│   │   │   ├── charts/
│   │   │   │   ├── LineChart.tsx
│   │   │   │   ├── BarChart.tsx
│   │   │   │   ├── PieChart.tsx
│   │   │   │   ├── AreaChart.tsx
│   │   │   │   ├── GaugeChart.tsx
│   │   │   │   ├── HeatmapChart.tsx
│   │   │   │   └── ChartWrapper.tsx
│   │   │   │
│   │   │   ├── kpi/
│   │   │   │   ├── KPICard.tsx
│   │   │   │   ├── KPITrendIndicator.tsx
│   │   │   │   ├── KPIProgressBar.tsx
│   │   │   │   ├── KPIComparison.tsx
│   │   │   │   └── KPIMetricDisplay.tsx
│   │   │   │
│   │   │   ├── notifications/
│   │   │   │   ├── NotificationBell.tsx
│   │   │   │   ├── NotificationList.tsx
│   │   │   │   ├── NotificationItem.tsx
│   │   │   │   └── NotificationPreferences.tsx
│   │   │   │
│   │   │   └── filters/
│   │   │       ├── DateRangeFilter.tsx
│   │   │       ├── DepartmentFilter.tsx
│   │   │       ├── KPIFilter.tsx
│   │   │       └── SavedFilters.tsx
│   │   │
│   │   ├── hooks/                    # Custom React hooks
│   │   │   ├── useAuth.ts
│   │   │   ├── usePermissions.ts
│   │   │   ├── useDebounce.ts
│   │   │   ├── useLocalStorage.ts
│   │   │   ├── useNotifications.ts
│   │   │   └── useDarkMode.ts
│   │   │
│   │   ├── utils/                    # Utility functions
│   │   │   ├── formatters.ts        # Number, date formatting
│   │   │   ├── validators.ts        # Form validation
│   │   │   ├── calculations.ts      # KPI calculations
│   │   │   ├── export.ts            # Export helpers
│   │   │   └── helpers.ts           # General helpers
│   │   │
│   │   └── styles/                   # Global styles
│   │       ├── index.css
│   │       ├── theme.ts             # MUI theme customization
│   │       └── variables.css
│   │
│   ├── public/
│   │   ├── index.html
│   │   └── favicon.ico
│   │
│   └── dist/                         # Build output (generated)
│
├── languages/                        # i18n (future)
│   └── kpi-dashboard.pot
│
└── docs/                             # Documentation
    ├── API.md
    ├── DATABASE.md
    ├── DEVELOPMENT.md
    └── USER_GUIDE.md
```

---

## 🔐 Security Considerations

### **Authentication & Authorization**
- Password hashing with bcrypt (PHP password_hash)
- JWT tokens with short expiry (15 min) + refresh tokens (7 days)
- CSRF protection on all state-changing operations
- Rate limiting on login attempts (5 attempts per 15 min)
- IP-based blocking for suspicious activity
- Session hijacking prevention (token rotation)

### **Data Security**
- SQL injection prevention (prepared statements)
- XSS protection (input sanitization, output escaping)
- File upload validation (type, size, malware scan)
- Audit logging for sensitive operations
- Encrypted data at rest (sensitive fields)
- HTTPS enforcement

### **Access Control**
- Role-based access control (RBAC)
- Permission checks on every API call
- Department-level data isolation
- User activity monitoring
- Automatic session expiry
- Force password change on first login

### **WordPress Specific**
- Nonces for WP admin actions
- Capability checks (if integrating with WP users)
- Sanitization using WP functions (sanitize_text_field, etc.)
- Database using $wpdb prepared statements

---

## 🚀 Development Phases

### **Phase 1: Foundation (Week 1-2)**
- [ ] Plugin structure setup
- [ ] Database schema & migrations
- [ ] Authentication system (login/logout/session)
- [ ] Basic routing (/kpi endpoint)
- [ ] User CRUD API
- [ ] Department CRUD API
- [ ] Basic React app setup with routing

### **Phase 2: Core Features (Week 3-4)**
- [ ] KPI definition management (CRUD)
- [ ] Position management
- [ ] KPI assignment system
- [ ] Data entry form & API
- [ ] Basic dashboard with charts
- [ ] User management UI
- [ ] Department management UI

### **Phase 3: Workflow & Approvals (Week 5-6)**
- [ ] Approval workflow implementation
- [ ] Notification system (in-app + email)
- [ ] Data validation & quality checks
- [ ] Audit logging
- [ ] Comments & collaboration
- [ ] Weekly reminder system

### **Phase 4: Analytics & Insights (Week 7-8)**
- [ ] Comparison features (YoY, MoM, departments)
- [ ] Best/Low performance insights
- [ ] Trend analysis
- [ ] Advanced charts & visualizations
- [ ] Personal dashboard
- [ ] Company overview dashboard

### **Phase 5: Reports & Export (Week 9-10)**
- [ ] Report templates
- [ ] Custom report builder
- [ ] PDF export (using TCPDF or similar)
- [ ] Excel export (PhpSpreadsheet)
- [ ] CSV export
- [ ] Scheduled reports system

### **Phase 6: Advanced Features (Week 11-12)**
- [ ] Alert rules configuration
- [ ] Custom notification preferences
- [ ] Dark mode implementation
- [ ] Mobile responsive optimization
- [ ] Search & advanced filters
- [ ] Bulk operations

### **Phase 7: Polish & Testing (Week 13-14)**
- [ ] Performance optimization
- [ ] Security audit
- [ ] Browser compatibility testing
- [ ] User acceptance testing (UAT)
- [ ] Bug fixes
- [ ] Documentation

### **Phase 8: Deployment & Training (Week 15-16)**
- [ ] Production deployment
- [ ] User training materials
- [ ] Admin guide
- [ ] Monitoring setup
- [ ] Backup strategy
- [ ] Support system

---

## 📊 Sample Data Structure Examples

### **Example: Sales Department KPIs**
```json
{
  "department": "Sales",
  "kpis": [
    {
      "name": "Monthly Revenue",
      "type": "department",
      "metric_type": "number",
      "unit": "IDR",
      "target": 1000000000,
      "input_frequency": "weekly"
    },
    {
      "name": "Conversion Rate",
      "type": "position",
      "assigned_to": "Sales Executive",
      "metric_type": "percentage",
      "target": 15,
      "input_frequency": "weekly"
    },
    {
      "name": "Customer Satisfaction",
      "type": "personal",
      "metric_type": "ratio",
      "unit": "/5",
      "target": 4.5,
      "input_frequency": "monthly"
    }
  ]
}
```

### **Example: Weekly Data Entry**
```json
{
  "kpi_id": 1,
  "department_id": 1,
  "user_id": 5,
  "period_start": "2025-11-03",
  "period_end": "2025-11-09",
  "value": 250000000,
  "notes": "Strong week due to new product launch",
  "attachments": [
    "/uploads/sales-report-week45.pdf"
  ],
  "status": "pending"
}
```

### **Example: Alert Rule**
```json
{
  "name": "Revenue Below Target Alert",
  "kpi_id": 1,
  "condition_type": "below_target",
  "threshold_value": 70,
  "threshold_operator": "<",
  "severity": "warning",
  "recipients": ["dept_head", "super_admin"],
  "notification_channels": ["email", "in_app"]
}
```

---

## 🎨 UI/UX Guidelines

### **Color Scheme (Corporate)**
```css
/* Light Mode */
--primary: #1976d2 (Blue - corporate)
--secondary: #dc004e (Accent red)
--success: #2e7d32 (Green)
--warning: #ed6c02 (Orange)
--error: #d32f2f (Red)
--background: #f5f5f5
--surface: #ffffff
--text-primary: #212121
--text-secondary: #757575

/* Dark Mode */
--primary: #90caf9
--secondary: #f48fb1
--success: #66bb6a
--warning: #ffa726
--error: #f44336
--background: #121212
--surface: #1e1e1e
--text-primary: #ffffff
--text-secondary: #b0b0b0
```

### **Typography**
- Primary Font: Inter or Roboto
- Headings: Semi-bold (600)
- Body: Regular (400)
- Scale: 12px/14px/16px/18px/20px/24px/32px

### **Spacing**
- Base unit: 8px
- Small: 8px
- Medium: 16px
- Large: 24px
- XLarge: 32px

### **Components Style**
- Border radius: 8px (cards, buttons)
- Shadows: Subtle elevation (MUI elevation 1-4)
- Transitions: 200ms ease-in-out
- Icons: 20-24px size

---

## 📝 Next Steps

1. **Review & Approve** this specification
2. **Clarify** any remaining questions
3. **Prioritize** features (MVP vs Nice-to-have)
4. **Setup** development environment
5. **Start** Phase 1 implementation

---

## ❓ Final Questions for You

1. **Timeline**: What's your target launch date? (Based on phases above, ~16 weeks for full implementation)

2. **MVP Scope**: Should we start with a minimum viable product (Phases 1-3) or full feature set?

3. **Design Assets**: Do you have:
   - Company logo?
   - Preferred color scheme?
   - Any existing design guidelines?

4. **Integration Requirements**: Any future integrations planned?
   - Email service (SendGrid, AWS SES, SMTP)?
   - Cloud storage (for file uploads)?
   - SSO (Single Sign-On)?

5. **Hosting Environment**:
   - Shared hosting or VPS/dedicated?
   - PHP version available?
   - MySQL version?
   - Server access level?

6. **Budget Considerations**: Any constraints that might affect technology choices?

7. **Testing Users**: Can you provide 5-10 test users for UAT phase?

Please review this comprehensive spec and let me know:
- ✅ What looks good
- ❌ What needs changes
- ❓ What needs clarification
- 🎯 Priority adjustments

Once you confirm, we'll create a detailed project plan and start coding! 🚀
