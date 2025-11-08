# KPI Dashboard WordPress Plugin

**Version:** 1.0.0
**Requires:** WordPress 6.4+, PHP 8.1+
**Author:** MBD Corp
**License:** GPL-2.0+

---

## 🚀 Quick Start (3 Steps)

### 1️⃣ Install Plugin
```bash
WordPress Admin → Plugins → Add New → Upload Plugin
```
Upload `kpi-dashboard.zip` dan activate.

### 2️⃣ Generate Data (Optional)
```bash
KPI Dashboard → System Info → Generate Dummy Data
```

### 3️⃣ Login
```
URL: https://your-site.com/kpi
User: admin
Pass: admin
```

**Done!** 🎉

---

## ✨ Features

- ✅ **User Management** - CRUD dengan role-based access
- ✅ **Department Management** - Struktur organisasi
- ✅ **KPI Management** - Define, assign, track KPIs
- ✅ **Data Entry** - Input KPI data dengan approval workflow
- ✅ **Analytics** - Dashboard, charts, trend analysis
- ✅ **Reports** - Export Excel/PDF
- ✅ **Notifications** - Real-time alerts
- ✅ **Audit Log** - Full activity tracking
- ✅ **Authentication** - JWT-based dengan auto-refresh

---

## 📋 Requirements

### Server
- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.3+
- WordPress 6.4+
- mod_rewrite enabled

### WordPress Settings
**Important:** Permalinks harus **Post name** atau custom structure.

```
Settings → Permalinks → Post name → Save
```

---

## 📦 What's Included

```
kpi-dashboard/
├── assets/
│   └── dist/              # Built React app (765 KB)
├── includes/
│   ├── api/               # REST API endpoints
│   ├── core/              # Auth, Router, Session
│   ├── models/            # Database models
│   └── services/          # Business logic
└── kpi-dashboard.php      # Main plugin file
```

---

## 🗄️ Database

Plugin auto-creates **16 tables** on activation:

- kpi_users
- kpi_departments
- kpi_positions
- kpi_definitions
- kpi_data
- kpi_sessions
- kpi_audit_logs
- kpi_notifications
- ... and 8 more

---

## 🔐 Default Access

After generating dummy data:

| Role | Username | Password |
|------|----------|----------|
| Super Admin | admin | admin |
| Dept Head | john.doe | password123 |
| Dept Head | jane.smith | password123 |
| Staff | Various | password123 |

**⚠️ Change passwords immediately in production!**

---

## 🌐 Access Points

### User Interface
```
https://your-site.com/kpi
```

### REST API
```
https://your-site.com/wp-json/kpi/v1/*
```

### Admin Panel
```
WordPress Admin → KPI Dashboard
```

---

## 🔧 Troubleshooting

### Blank Page at /kpi

1. Check permalinks: **Settings → Permalinks → Save**
2. Clear browser cache: `Ctrl + Shift + Delete`
3. Hard reload: `Ctrl + F5`

### "Not authenticated" Error

1. Logout and login again
2. Clear browser cache
3. Check browser console (F12) for errors

### 404 Not Found

1. Flush permalinks: **Settings → Permalinks → Save**
2. Check `.htaccess` file exists
3. Verify mod_rewrite is enabled

---

## 🧪 Testing Tools

Plugin includes diagnostic scripts:

### Complete Validation
```
https://your-site.com/validate-kpi-complete.php?key=validate-2024
```

### Auth API Test
```
https://your-site.com/test-auth-api.php?key=test-auth
```

### Diagnostic
```
https://your-site.com/fix-blank-page.php?key=fix-blank-2024
```

---

## 📚 Documentation

### For Users
- [Installation Guide](DEPLOYMENT-READY.md)
- [User Manual](#) - Coming soon
- [Video Tutorials](#) - Coming soon

### For Developers
- [API Documentation](#) - Built-in at `/kpi/api-docs`
- [Database Schema](#)
- [Development Guide](#)

---

## 🔄 Updates

### Current Version: 1.0.0

#### Fixed in 1.0.0
- ✅ Authentication double nesting issue
- ✅ Path detection in diagnostic scripts
- ✅ React root element ID mismatch
- ✅ Token refresh mechanism

#### Upcoming
- 🔜 Multi-language support
- 🔜 Advanced analytics
- 🔜 Mobile app
- 🔜 Email reports

---

## 🆘 Support

### Get Help
- **Documentation:** [DEPLOYMENT-READY.md](DEPLOYMENT-READY.md)
- **Issues:** GitHub Issues
- **Email:** support@mbdcorp.id

### Report Bugs
1. Go to GitHub Issues
2. Describe the problem
3. Include screenshots
4. Mention WordPress & PHP version

---

## 📄 License

GPL-2.0+

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation.

---

## 👨‍💻 Credits

**Developed by:** MBD Corp
**Website:** https://www.mbdcorp.id

### Tech Stack
- **Backend:** PHP 8.1, WordPress REST API
- **Frontend:** React 18, TypeScript, Material-UI
- **Build:** Vite
- **State:** Zustand
- **Auth:** JWT

---

## ⭐ Show Your Support

If you find this plugin useful, please:
- ⭐ Star this repo
- 🐛 Report bugs
- 💡 Suggest features
- 📢 Share with others

---

## 🎉 Ready to Go!

**No configuration needed. Just install and use!**

```bash
1. Upload plugin ✓
2. Activate ✓
3. Access /kpi ✓
4. Login ✓
5. Start managing KPIs! 🚀
```

---

**Built with ❤️ by MBD Corp**
