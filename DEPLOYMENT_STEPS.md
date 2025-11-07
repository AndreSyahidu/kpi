# 🚀 LANGKAH IMPLEMENTASI KE SERVER PRODUCTION

## ⚠️ PENTING: Kenapa Masih Placeholder?

File-file baru yang sudah dibuat masih di local/development. Anda perlu upload ke server production di **www.mbdcorp.id**

---

## 📦 STEP 1: Siapkan File yang Perlu Di-Upload

Gunakan FTP/cPanel File Manager untuk upload file-file berikut:

### A. Built Assets (PALING PENTING!)

Upload folder ini:
```
kpi-dashboard/assets/dist/
```

Ke server di:
```
/wp-content/plugins/kpi-dashboard/assets/dist/
```

**File yang harus ada di server:**
- `assets/dist/.vite/manifest.json`
- `assets/dist/assets/main-Cn-9-PkT.js` (765 KB)
- `assets/dist/assets/main-BIgbKKtm.css` (219 bytes)

### B. Page Files (Halaman Baru)

Upload semua file di folder:
```
kpi-dashboard/assets/src/pages/
```

**Files yang di-update/buat:**
- `pages/kpis/KPIsPage.tsx` (283 lines) ← BARU
- `pages/settings/SettingsPage.tsx` (548 lines) ← BARU
- `pages/data-entry/DataEntryPage.tsx` (622 lines) ← BARU
- `pages/approvals/ApprovalsPage.tsx` (495 lines) ← BARU
- `pages/notifications/NotificationsPage.tsx` (316 lines) ← BARU
- `pages/reports/ReportsPage.tsx` (409 lines) ← BARU
- `pages/analytics/AnalyticsPage.tsx` (397 lines) ← BARU

### C. Service Files

Upload folder:
```
kpi-dashboard/assets/src/services/
```

**Semua service files ini HARUS ada:**
- `services/users.service.ts`
- `services/departments.service.ts`
- `services/kpis.service.ts`
- `services/data.service.ts`
- `services/approvals.service.ts`
- `services/reports.service.ts`
- `services/notifications.service.ts`
- `services/settings.service.ts`
- `services/analytics.service.ts`

### D. Store Files

Upload:
```
kpi-dashboard/assets/src/store/auth.store.ts
```

Ke:
```
/wp-content/plugins/kpi-dashboard/assets/src/store/
```

### E. Dependencies

Upload:
```
kpi-dashboard/package.json
kpi-dashboard/package-lock.json
```

---

## 🔧 STEP 2: Install Dependencies di Server

**Via SSH (jika ada akses):**
```bash
cd /path/to/wp-content/plugins/kpi-dashboard
npm install --legacy-peer-deps
```

**ATAU Via cPanel Terminal:**
1. Login ke cPanel
2. Buka Terminal
3. Jalankan:
```bash
cd public_html/wp-content/plugins/kpi-dashboard
npm install --legacy-peer-deps
```

**ATAU (Lebih Mudah):**
Upload folder `node_modules` langsung jika server tidak support npm

---

## 🔄 STEP 3: Clear Cache & Restart

### A. Clear WordPress Cache

1. Login ke WordPress Admin
2. Pergi ke plugin cache yang dipakai (WP Super Cache, W3 Total Cache, dll)
3. Klik "Clear All Cache" atau "Purge Cache"

### B. Deactivate & Reactivate Plugin

1. Pergi ke **Plugins** di WordPress Admin
2. Cari "KPI Dashboard"
3. Klik "Deactivate"
4. Tunggu 3 detik
5. Klik "Activate"

### C. Flush Permalinks

**Opsi 1 - Via WordPress Admin:**
1. Pergi ke **Settings → Permalinks**
2. Scroll ke bawah
3. Klik "Save Changes" (tidak perlu ubah apa-apa)

**Opsi 2 - Via Helper Script:**
Buka di browser:
```
https://www.mbdcorp.id/wp-content/plugins/kpi-dashboard/flush-rewrite.php
```

---

## 🌐 STEP 4: Clear Browser Cache

1. Tekan `Ctrl + Shift + Delete` (Windows) atau `Cmd + Shift + Delete` (Mac)
2. Pilih "Cached images and files"
3. Klik "Clear data"

**ATAU lebih mudah:**
- Tekan `Ctrl + F5` (hard reload)
- Atau buka di Incognito/Private mode

---

## ✅ STEP 5: Test & Verify

Buka halaman-halaman ini dan pastikan TIDAK ada placeholder:

1. https://www.mbdcorp.id/kpi/ (Dashboard)
2. https://www.mbdcorp.id/kpi/users
3. https://www.mbdcorp.id/kpi/departments
4. https://www.mbdcorp.id/kpi/kpis
5. https://www.mbdcorp.id/kpi/data-entry
6. https://www.mbdcorp.id/kpi/approvals
7. https://www.mbdcorp.id/kpi/reports
8. https://www.mbdcorp.id/kpi/analytics
9. https://www.mbdcorp.id/kpi/notifications
10. https://www.mbdcorp.id/kpi/settings

**Yang harus terlihat:**
- ✅ Form tambah data
- ✅ Tabel data
- ✅ Tombol edit/hapus
- ✅ BUKAN tulisan "Backend ready!"

---

## 🚨 TROUBLESHOOTING

### Masalah: Masih tampil placeholder

**Solusi:**
1. Cek apakah file `assets/dist/.vite/manifest.json` sudah ter-upload
2. Cek browser console (F12) untuk error JavaScript
3. Hard reload dengan Ctrl+F5
4. Clear cache WordPress
5. Deactivate/reactivate plugin

### Masalah: Error "Failed to load module"

**Solusi:**
1. Pastikan folder `node_modules` ter-upload ATAU
2. Jalankan `npm install --legacy-peer-deps` di server

### Masalah: 404 Not Found

**Solusi:**
1. Flush permalinks (Settings → Permalinks → Save)
2. Atau jalankan flush-rewrite.php

### Masalah: Blank page

**Solusi:**
1. Cek error log di cPanel
2. Pastikan PHP version ≥ 8.1
3. Pastikan file router.php ter-update

---

## 📋 CHECKLIST AKHIR

Sebelum test, pastikan sudah:

- [ ] Upload folder `assets/dist/` (dengan file .js dan .css)
- [ ] Upload folder `assets/src/pages/` (semua page files)
- [ ] Upload folder `assets/src/services/` (semua service files)
- [ ] Upload file `assets/src/store/auth.store.ts`
- [ ] Upload `package.json` (yang baru dengan dayjs)
- [ ] Install dependencies atau upload `node_modules`
- [ ] Clear WordPress cache
- [ ] Deactivate & reactivate plugin
- [ ] Flush permalinks
- [ ] Clear browser cache
- [ ] Hard reload (Ctrl+F5)

---

## 💡 CARA TERCEPAT (Recommended)

**Jika pakai cPanel File Manager:**

1. **Backup dulu plugin yang lama:**
   - Zip folder `/wp-content/plugins/kpi-dashboard`
   - Download sebagai backup

2. **Upload semua file sekaligus:**
   - Zip folder `kpi-dashboard` yang baru dari local
   - Upload zip ke `/wp-content/plugins/`
   - Extract (Replace all)

3. **Clear cache & test:**
   - Deactivate/reactivate plugin
   - Flush permalinks
   - Clear browser cache
   - Test semua halaman

**Total waktu: ±5-10 menit**

---

## ✅ EXPECTED RESULT

Setelah semua langkah, halaman KPI Dashboard akan menampilkan:

✅ **Users Page:** Tabel users, tombol Add User, search, filter role
✅ **Departments:** Tabel departments dengan color picker
✅ **KPIs:** Tabel KPI definitions, form tambah KPI
✅ **Data Entry:** Form input data dengan date picker
✅ **Approvals:** Queue pending approvals, tombol approve/reject
✅ **Reports:** Form generate report, pilihan PDF/Excel
✅ **Analytics:** Metric cards, charts, insights
✅ **Notifications:** List notifications dengan badge unread
✅ **Settings:** Form profile, change password, preferences

**TIDAK ADA lagi tulisan "Backend ready!"**

---

*Jika masih ada masalah setelah langkah-langkah ini, screenshot error yang muncul dan console error (F12).*
