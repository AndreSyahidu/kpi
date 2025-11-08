# KPI Dashboard - Ready to Deploy

## 🚀 Instalasi Super Cepat (5 Menit)

### Step 1: Upload Plugin

1. **Download** `kpi-dashboard.zip` dari GitHub Releases
2. **Login** ke WordPress Admin
3. **Plugins** → **Add New** → **Upload Plugin**
4. **Choose File** → pilih `kpi-dashboard.zip`
5. **Install Now** → **Activate**

### Step 2: Buat Dummy Data (Opsional)

Di WordPress Admin:
1. **KPI Dashboard** → **System Info**
2. Klik **"Generate Dummy Data"**
3. Tunggu sampai selesai (±30 detik)

### Step 3: Akses Dashboard

Buka browser:
```
https://www.mbdcorp.id/kpi
```

**Default Login:**
- Username: `admin`
- Password: `admin`

---

## ✅ Fitur Utama

### 1. **Authentication System**
- ✅ JWT-based authentication
- ✅ Auto token refresh (15 menit)
- ✅ Session management
- ✅ Role-based access (Super Admin, Dept Head, Staff)

### 2. **User Management**
- ✅ CRUD users dengan roles
- ✅ Department assignment
- ✅ Position assignment
- ✅ Avatar upload
- ✅ Active/inactive status

### 3. **Department Management**
- ✅ CRUD departments
- ✅ Department heads assignment
- ✅ Hierarchy structure

### 4. **KPI Management**
- ✅ CRUD KPI definitions
- ✅ Multiple calculation types (sum, avg, count, percentage)
- ✅ Target setting
- ✅ Weight assignment
- ✅ Active/inactive status

### 5. **Data Entry**
- ✅ Input KPI data (daily, weekly, monthly, quarterly, yearly)
- ✅ Approval workflow
- ✅ Pending/approved/rejected status
- ✅ Comments & notes

### 6. **Approvals**
- ✅ Department head approval
- ✅ Batch approval
- ✅ Rejection with notes
- ✅ Approval history

### 7. **Reports & Analytics**
- ✅ Department performance
- ✅ Individual performance
- ✅ Trend analysis
- ✅ Export to Excel/PDF

### 8. **Notifications**
- ✅ Real-time notifications
- ✅ Email notifications
- ✅ Pending approvals alert
- ✅ Target achievement alerts

### 9. **Audit Log**
- ✅ All user actions logged
- ✅ Login/logout tracking
- ✅ Data changes tracking
- ✅ Full audit trail

---

## 🔧 Technical Stack

### Backend
- **PHP**: 8.1+
- **WordPress**: 6.4+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **REST API**: Custom REST endpoints (`/wp-json/kpi/v1/*`)

### Frontend
- **React**: 18.x
- **TypeScript**: 5.x
- **Material-UI**: 5.x
- **React Router**: 6.x
- **Zustand**: State management
- **Axios**: HTTP client
- **Vite**: Build tool

### Security
- **JWT Authentication**: HS256 algorithm
- **Password Hashing**: bcrypt
- **SQL Injection Protection**: wpdb prepared statements
- **XSS Protection**: WordPress sanitization
- **CSRF Protection**: Nonce validation
- **Rate Limiting**: 5 failed login attempts = 15 min lockout

---

## 📁 File Structure

```
kpi-dashboard/
├── assets/
│   ├── dist/                    # Built frontend (production)
│   │   ├── .vite/
│   │   │   └── manifest.json
│   │   └── assets/
│   │       ├── main-*.js       # React bundle (765 KB)
│   │       └── main-*.css      # Styles
│   └── src/                     # React source code
│       ├── components/
│       ├── pages/
│       ├── services/
│       ├── store/
│       └── main.tsx
├── includes/
│   ├── api/                     # REST API endpoints
│   ├── core/                    # Auth, Router, Session
│   ├── models/                  # Database models
│   ├── services/                # Business logic
│   └── utils/                   # Helpers
└── kpi-dashboard.php            # Main plugin file
```

---

## 🗄️ Database Tables

Plugin otomatis membuat 16 tables:

| Table | Purpose |
|-------|---------|
| `kpi_users` | User accounts |
| `kpi_departments` | Department list |
| `kpi_positions` | Job positions |
| `kpi_department_heads` | Dept head assignments |
| `kpi_definitions` | KPI definitions |
| `kpi_assignments` | KPI assignments to users |
| `kpi_data` | KPI data entries |
| `kpi_audit_logs` | Audit trail |
| `kpi_notifications` | User notifications |
| `kpi_alert_rules` | Alert configurations |
| `kpi_user_notification_prefs` | User preferences |
| `kpi_scheduled_reports` | Scheduled reports |
| `kpi_report_history` | Report history |
| `kpi_comments` | Comments on data |
| `kpi_settings` | System settings |
| `kpi_sessions` | Active sessions |

---

## 🔐 Default Users (Dummy Data)

Setelah generate dummy data, tersedia 23 users:

### Super Admins
- `admin` / `admin` - System Administrator

### Department Heads
- `john.doe` / `password123` - Finance Head
- `jane.smith` / `password123` - HR Head
- `bob.johnson` / `password123` - IT Head
- `alice.williams` / `password123` - Sales Head
- `charlie.brown` / `password123` - Marketing Head

### Staff
- 17 staff members dengan berbagai roles

---

## 🌐 API Endpoints

### Authentication
```
POST   /wp-json/kpi/v1/auth/login
POST   /wp-json/kpi/v1/auth/logout
POST   /wp-json/kpi/v1/auth/refresh
GET    /wp-json/kpi/v1/auth/me
POST   /wp-json/kpi/v1/auth/change-password
POST   /wp-json/kpi/v1/auth/forgot-password
POST   /wp-json/kpi/v1/auth/reset-password
```

### Users
```
GET    /wp-json/kpi/v1/users
GET    /wp-json/kpi/v1/users/:id
POST   /wp-json/kpi/v1/users
PUT    /wp-json/kpi/v1/users/:id
DELETE /wp-json/kpi/v1/users/:id
```

### Departments
```
GET    /wp-json/kpi/v1/departments
GET    /wp-json/kpi/v1/departments/:id
POST   /wp-json/kpi/v1/departments
PUT    /wp-json/kpi/v1/departments/:id
DELETE /wp-json/kpi/v1/departments/:id
```

### KPIs
```
GET    /wp-json/kpi/v1/kpis
GET    /wp-json/kpi/v1/kpis/:id
POST   /wp-json/kpi/v1/kpis
PUT    /wp-json/kpi/v1/kpis/:id
DELETE /wp-json/kpi/v1/kpis/:id
```

### Data Entry
```
GET    /wp-json/kpi/v1/data
GET    /wp-json/kpi/v1/data/:id
POST   /wp-json/kpi/v1/data
PUT    /wp-json/kpi/v1/data/:id
DELETE /wp-json/kpi/v1/data/:id
```

### Approvals
```
GET    /wp-json/kpi/v1/approvals/pending
POST   /wp-json/kpi/v1/approvals/:id/approve
POST   /wp-json/kpi/v1/approvals/:id/reject
POST   /wp-json/kpi/v1/approvals/batch-approve
```

### Analytics & Reports
```
GET    /wp-json/kpi/v1/analytics/overview
GET    /wp-json/kpi/v1/analytics/department/:id
GET    /wp-json/kpi/v1/analytics/user/:id
GET    /wp-json/kpi/v1/reports/generate
GET    /wp-json/kpi/v1/reports/export
```

---

## ⚙️ Configuration

### WordPress Requirements
```php
PHP >= 8.1
WordPress >= 6.4
MySQL >= 5.7 or MariaDB >= 10.3
mod_rewrite enabled
```

### Recommended PHP Extensions
```
- bcmath
- json
- mbstring
- mysqli
- openssl
- zip
```

### WordPress Permalink Settings
**IMPORTANT**: Permalink harus menggunakan **"Post name"** atau custom structure.

Dashboard tidak akan bekerja jika menggunakan **"Plain"** permalinks.

**Fix:**
1. WordPress Admin → **Settings** → **Permalinks**
2. Pilih **"Post name"**
3. **Save Changes**

---

## 🐛 Troubleshooting

### Issue: Blank Page di /kpi

**Solusi:**
1. Flush permalinks: **Settings** → **Permalinks** → **Save**
2. Clear browser cache: `Ctrl + Shift + Delete`
3. Hard reload: `Ctrl + F5`

### Issue: "Not authenticated" Error

**Solusi:**
1. Logout dan login kembali
2. Clear browser cache
3. Check browser console (F12) untuk error
4. Verify token di localStorage

### Issue: 404 Not Found

**Solusi:**
1. Check `.htaccess` file exists
2. Verify mod_rewrite enabled
3. Flush permalinks
4. Check WordPress site URL

### Issue: Database Tables Missing

**Solusi:**
1. Deactivate plugin
2. Activate plugin lagi
3. Check PHP error log
4. Manually run activation: `wp kpi activate`

---

## 🧪 Testing Tools

Plugin dilengkapi testing scripts:

### 1. Complete Validation
```
https://www.mbdcorp.id/validate-kpi-complete.php?key=validate-2024
```
Tests:
- ✅ Plugin files
- ✅ Build files
- ✅ Database tables
- ✅ API endpoints
- ✅ Permissions

### 2. Auth API Test
```
https://www.mbdcorp.id/test-auth-api.php?key=test-auth
```
Tests:
- ✅ Login flow
- ✅ Token validation
- ✅ Protected endpoints
- ✅ Authorization headers

### 3. Diagnostic Script
```
https://www.mbdcorp.id/fix-blank-page.php?key=fix-blank-2024
```
Tests:
- ✅ Page rendering
- ✅ React mount
- ✅ Script loading
- ✅ API connectivity

---

## 📊 Performance

### Frontend
- Initial load: ~800 KB (JS + CSS)
- Gzipped: ~250 KB
- Load time: < 2s (on fast connection)

### Backend
- Average API response: < 100ms
- Database queries: Optimized with indexes
- Caching: Transient API for expensive queries

### Optimization
- Code splitting: ✅ (Vite automatic)
- Lazy loading: ✅ (React.lazy for routes)
- Asset minification: ✅ (Vite production build)
- Image optimization: ✅ (WebP support)

---

## 🔄 Updates

### Manual Update
1. Download latest version
2. Deactivate current plugin
3. Delete old plugin files
4. Upload new version
5. Activate plugin
6. Database akan auto-migrate

### Via WordPress
**Coming soon**: Update via WordPress dashboard

---

## 🆘 Support

### Documentation
- GitHub Wiki: [Link to wiki]
- API Documentation: Built-in at `/kpi/api-docs`

### Bug Reports
- GitHub Issues: [Link to issues]
- Email: support@mbdcorp.id

### Feature Requests
- GitHub Discussions: [Link to discussions]

---

## 📝 Changelog

### Version 1.0.0 (Current)
- ✅ Initial release
- ✅ Complete authentication system
- ✅ User & department management
- ✅ KPI definitions & assignments
- ✅ Data entry with approvals
- ✅ Reports & analytics
- ✅ Notifications system
- ✅ Audit logging

### Upcoming Features
- 🔜 Multi-language support
- 🔜 Advanced analytics dashboard
- 🔜 Mobile app (React Native)
- 🔜 Email digest reports
- 🔜 Integration with external systems

---

## 📄 License

GPL-2.0+

Copyright (c) 2024 MBD Corp

---

## ✅ Pre-Deployment Checklist

Before deploying to production:

- [ ] PHP 8.1+ installed
- [ ] WordPress 6.4+ installed
- [ ] MySQL/MariaDB running
- [ ] mod_rewrite enabled
- [ ] Permalinks set to "Post name"
- [ ] SSL certificate installed (HTTPS)
- [ ] Backup database
- [ ] Test in staging first

---

## 🎉 You're Ready!

Plugin sudah 100% siap pakai. Tidak perlu konfigurasi tambahan.

**Just upload, activate, and go!** 🚀
