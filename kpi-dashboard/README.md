# KPI Dashboard WordPress Plugin

Comprehensive KPI Management System for businesses with up to 100+ employees. Standalone dashboard with full user management, department tracking, approval workflows, and advanced analytics.

---

## 📖 Documentation

**Getting Started:**
- **[Quick Start Guide](QUICKSTART.md)** - Get up and running in 10 minutes
- **[Installation Guide](docs/INSTALLATION.md)** - Complete installation instructions
- **[Deployment Guide](docs/DEPLOYMENT.md)** - Production deployment best practices

**Reference:**
- **[API Documentation](docs/API.md)** - Full REST API reference
- **[Testing Guide](TESTING.md)** - Testing strategies and test cases
- **[Changelog](CHANGELOG.md)** - Version history and changes

**Development:**
- **[Implementation Summary](IMPLEMENTATION_SUMMARY.md)** - Technical architecture
- **[Environment Template](.env.example)** - Configuration options

---

## 🚀 Features

### Core Functionality
- **Standalone Access**: Independent login system at `domain.com/kpi` (no wp-admin required)
- **4-Level User Roles**: Super Admin, Department Head, Manager, Staff with granular permissions
- **Department & Position Management**: Hierarchical structure with unlimited depth
- **Flexible KPI System**: Department, Position, and Personal level KPIs
- **Approval Workflow**: Data entry → Review → Approve/Reject cycle
- **3-Year Data Retention**: Historical tracking with automatic cleanup

### User Management
- Complete CRUD operations
- Role-based access control (RBAC)
- Department and position assignment
- Bulk import from CSV
- Welcome emails
- Password reset functionality

### KPI Management
- Multiple KPI types (department/position/personal)
- Various metric types (number/percentage/ratio/boolean/custom)
- Customizable targets (minimum/maximum/range)
- Input frequency configuration (daily/weekly/monthly/quarterly/yearly)
- Validation rules
- Assignment system

### Analytics & Insights
- Company overview dashboard
- Best & low performing departments
- Trend analysis (6-month default)
- Department comparison
- Individual top performers
- AI-like insights and recommendations
- Achievement percentage calculations

### Reports & Export
- Department performance reports
- Executive summary reports
- KPI detail reports
- Multiple formats (CSV, JSON)
- Scheduled automated reports
- Report history tracking

### Notifications
- In-app notifications
- Email notifications
- Customizable alert rules
- Weekly reminder system
- User preferences management
- Multiple severity levels

### Additional Features
- Dark mode support
- Fully responsive design
- Real-time notifications
- Audit trail for compliance
- Search and advanced filtering
- Bulk operations
- File attachments
- Comments & collaboration

## 📋 Requirements

- **WordPress**: 6.4 or higher
- **PHP**: 8.1 or higher
- **MySQL**: 8.0 or higher / MariaDB 10.6+
- **Node.js**: 18+ (for development)

## 🔧 Quick Installation

### Automated Setup

```bash
# Clone repository
cd wp-content/plugins/
git clone <repository-url> kpi-dashboard
cd kpi-dashboard

# Run automated setup
./scripts/setup.sh check      # Check requirements
./scripts/setup.sh install    # Install dependencies
./scripts/setup.sh build      # Build for production
```

### Manual Installation

1. **Upload plugin** to `/wp-content/plugins/kpi-dashboard/`
2. **Install dependencies**: `npm install --legacy-peer-deps`
3. **Build frontend**: `npm run build`
4. **Activate plugin** in WordPress Admin → Plugins
5. **Access dashboard** at `https://yourdomain.com/kpi`

**Default Credentials:**
- Username: `admin`
- Password: `admin`

⚠️ **Important**: Change the default password immediately after first login!

For detailed installation instructions, see **[Installation Guide](docs/INSTALLATION.md)**

## 🏗️ Technical Architecture

### Backend (PHP)

```
Plugin Structure:
├── kpi-dashboard.php          # Main plugin file
├── includes/
│   ├── core/                  # Authentication, Router, Permissions
│   ├── models/                # Data models (7 classes)
│   ├── services/              # Business logic (10 services)
│   ├── api/                   # REST API (12 endpoints)
│   ├── database/              # Schema & migrations
│   ├── utils/                 # Validator, Sanitizer, Logger, etc
│   └── admin/                 # WordPress admin integration
```

### Frontend (React + TypeScript)

```
Frontend Structure:
├── assets/src/
│   ├── config/                # API & theme configuration
│   ├── types/                 # TypeScript interfaces
│   ├── store/                 # Zustand state management
│   ├── services/              # API service layer
│   ├── routes/                # React Router setup
│   ├── layouts/               # Layout components
│   ├── pages/                 # Page components
│   ├── components/            # Reusable components
│   ├── hooks/                 # Custom React hooks
│   └── utils/                 # Utility functions
```

### Database

**16 Custom Tables:**
- `wp_kpi_users` - User management
- `wp_kpi_departments` - Department hierarchy
- `wp_kpi_positions` - Position roles
- `wp_kpi_department_heads` - Head assignments
- `wp_kpi_definitions` - KPI definitions
- `wp_kpi_assignments` - KPI assignments
- `wp_kpi_data` - KPI data entries
- `wp_kpi_audit_logs` - Activity tracking
- `wp_kpi_notifications` - User notifications
- `wp_kpi_alert_rules` - Alert configurations
- `wp_kpi_user_notification_prefs` - User preferences
- `wp_kpi_scheduled_reports` - Report schedules
- `wp_kpi_report_history` - Report archives
- `wp_kpi_comments` - Collaboration
- `wp_kpi_settings` - System settings
- `wp_kpi_sessions` - User sessions

### REST API Endpoints

**Base URL:** `/wp-json/kpi/v1`

**Authentication:** JWT Bearer Token

**Endpoints:**
- `POST /auth/login` - User login
- `POST /auth/logout` - Logout
- `GET /auth/me` - Current user info
- `GET|POST|PUT|DELETE /users` - User management
- `GET|POST|PUT|DELETE /departments` - Department management
- `GET|POST|PUT|DELETE /positions` - Position management
- `GET|POST|PUT|DELETE /kpis` - KPI management
- `GET|POST|PUT|DELETE /data` - KPI data entries
- `GET /approvals` - Pending approvals queue
- `POST /approvals/{id}/approve` - Approve entry
- `POST /approvals/{id}/reject` - Reject entry
- `GET /analytics/*` - Analytics endpoints
- `POST /reports/*` - Report generation
- `GET /notifications` - User notifications
- `GET|PUT /settings` - System settings

## 🎨 Technology Stack

### Backend
- **WordPress** 6.4+
- **PHP** 8.1+
- **MySQL** 8.0+
- Custom REST API with JWT authentication

### Frontend
- **React** 18
- **TypeScript** 5
- **Material-UI** 5 (MUI)
- **Zustand** (State management)
- **React Router** 6
- **ApexCharts** (Data visualization)
- **Axios** (HTTP client)
- **Vite** (Build tool)

## 🔐 Security

- JWT-based authentication
- Password hashing with bcrypt
- CSRF protection
- SQL injection prevention (prepared statements)
- XSS protection (input sanitization)
- Rate limiting on login attempts
- Session management with IP tracking
- Audit logging for sensitive operations
- Role-based access control (RBAC)
- Encrypted data at rest (sensitive fields)

## 📊 Default Data

Upon activation, the plugin creates:

**Super Admin User:**
- Username: `admin`
- Password: `admin`
- Role: Super Admin

**6 Default Departments:**
- Sales (Green #4CAF50)
- Marketing (Blue #2196F3)
- Operations (Orange #FF9800)
- Human Resources (Purple #9C27B0)
- Finance (Red #F44336)
- IT (Gray #607D8B)

## 🎯 Usage Examples

### Creating a KPI

```php
// Via Service Layer
$kpi = KPI_Dashboard_KPI_Service::create([
    'name' => 'Monthly Sales Revenue',
    'category' => 'sales',
    'type' => 'department',
    'metric_type' => 'number',
    'unit' => 'IDR',
    'input_frequency' => 'monthly',
    'target_value' => 1000000000,
    'target_type' => 'minimum',
], $current_user_id);
```

### Creating a Data Entry

```php
$entry = KPI_Dashboard_Data_Service::create_entry([
    'kpi_id' => 1,
    'department_id' => 1,
    'period_start' => '2025-01-01',
    'period_end' => '2025-01-31',
    'value' => 1200000000,
    'notes' => 'Strong month!',
], $user_id);
```

### Generating Reports

```php
$report = KPI_Dashboard_Report_Generator::generate_department_report(
    $department_id,
    '2025-01-01',
    '2025-01-31',
    'csv'
);
```

## 🛠️ Development

### Setup Development Environment

```bash
# Install dependencies
npm install

# Start development server
npm run dev

# The frontend will be available at http://localhost:5173
# It will proxy API requests to your WordPress installation
```

### Building for Production

```bash
# Build optimized production bundle
npm run build

# Preview production build
npm run preview
```

### Code Quality

```bash
# Run linter
npm run lint

# TypeScript type checking
npx tsc --noEmit
```

### Quick Start Scripts

Use the setup script for common tasks:

```bash
# Check requirements
./scripts/setup.sh check

# Install dependencies
./scripts/setup.sh install

# Build for production
./scripts/setup.sh build

# Start development server
./scripts/setup.sh dev

# Create deployment package
./scripts/setup.sh package

# Show system info
./scripts/setup.sh info
```

### WP-CLI Commands

```bash
# Install/reinstall database tables
wp kpi-dashboard install

# Generate test data
wp kpi-dashboard generate-data

# Clear test data
wp kpi-dashboard clear-data

# Create a new user
wp kpi-dashboard create-user --username=johndoe --email=john@example.com

# Show plugin information
wp kpi-dashboard info

# Export data to CSV
wp kpi-dashboard export --type=users --output=users.csv

# Reset plugin to initial state
wp kpi-dashboard reset --yes
```

Example API Request:

```javascript
// Login
const response = await fetch('/wp-json/kpi/v1/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    username: 'admin',
    password: 'admin'
  })
});

const { data } = await response.json();
const { access_token, user } = data;

// Authenticated request
const users = await fetch('/wp-json/kpi/v1/users', {
  headers: {
    'Authorization': `Bearer ${access_token}`
  }
});
```

## 🤝 Contributing

This is a proprietary plugin for MBD Corp. Internal use only.

## 📄 License

Proprietary - All Rights Reserved © 2025 MBD Corp

## 🆘 Support

For support, please contact:
- Email: support@mbdcorp.id
- Website: https://www.mbdcorp.id

## 🔄 Version History

### Version 1.0.0 (2025-01-06)
- ✅ Initial release
- ✅ Complete backend infrastructure
- ✅ REST API layer (12 endpoints)
- ✅ React frontend with TypeScript
- ✅ Authentication system
- ✅ User management
- ✅ Department management
- ✅ KPI system
- ✅ Data entry & approval workflow
- ✅ Analytics & insights
- ✅ Report generation
- ✅ Notification system
- ✅ Audit logging
- ✅ Dark mode support

## 🎯 Roadmap

### Future Enhancements
- [ ] Advanced data visualization (more chart types)
- [ ] Mobile app (React Native)
- [ ] Excel/CSV bulk import
- [ ] PDF report templates
- [ ] Integration with Google Analytics
- [ ] WooCommerce integration
- [ ] Two-factor authentication (2FA)
- [ ] Advanced role customization
- [ ] Multi-language support (i18n)
- [ ] Real-time dashboard updates (WebSockets)

---

**Built with ❤️ for MBD Corp by Claude Code**
