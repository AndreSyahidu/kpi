# Fix Content Security Policy (CSP) Blocking JavaScript

## 🔍 Masalah

Browser menampilkan error:
```
The Content Security Policy (CSP) prevents the evaluation of arbitrary strings
as JavaScript to make it more difficult for an attacker to inject unauthorized
code on your site.
```

Error ini muncul karena:
1. WordPress security plugin (WordFence, iThemes Security, dll) set CSP headers
2. Server/hosting punya CSP policy yang strict
3. React build code pakai eval() atau new Function()

---

## ✅ Solusi - Pilih Salah Satu

### **Solusi 1: Whitelist /kpi Route di Security Plugin** (Recommended)

Jika pakai **WordFence**, **iThemes Security**, atau **All In One WP Security**:

#### WordFence:
1. Login WordPress Admin
2. Go to **WordFence → Firewall → All Firewall Options**
3. Scroll ke **Content Security Policy**
4. Tambahkan exception untuk `/kpi`:
   ```
   script-src 'self' 'unsafe-inline' 'unsafe-eval';
   ```
5. Atau **disable CSP** sementara untuk testing

#### iThemes Security:
1. Go to **Security → Settings → System Tweaks**
2. Find **Content Security Policy**
3. Add exception atau disable untuk `/kpi` route

#### All In One WP Security:
1. Go to **WP Security → Firewall**
2. Disable **6G/7G Firewall** sementara
3. Test lagi

---

### **Solusi 2: Tambah CSP Header di Router** (Quick Fix)

Edit plugin untuk inject CSP header yang proper:

File: `includes/core/class-router.php`

Tambahkan di method `serve_prod_app()` sebelum HTML:

```php
// Set CSP headers to allow our scripts
header("Content-Security-Policy: script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';");
```

---

### **Solusi 3: Disable CSP via .htaccess**

Jika pakai Apache, tambahkan di `.htaccess`:

```apache
# Allow JavaScript for KPI Dashboard
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} ^/kpi
    RewriteRule .* - [E=no_csp:1]
</IfModule>

<IfModule mod_headers.c>
    Header unset Content-Security-Policy env=no_csp
</IfModule>
```

---

### **Solusi 4: Check & Rebuild Frontend** (Pastikan Production Build)

Production build React **tidak pakai eval()**, jadi kalau masih error CSP, kemungkinan build belum production mode.

Rebuild:
```bash
cd ~/mbdcorp.id/wp-content/plugins/kpi-dashboard

# Clean dulu
rm -rf assets/dist

# Extract lagi
tar -xzf assets-dist-prebuilt.tar.gz

# Verify
ls -la assets/dist/assets/
```

---

### **Solusi 5: Find & Disable CSP Source**

Check plugin mana yang inject CSP:

```bash
# Via WP-CLI
wp plugin list

# Cari plugin security yang aktif:
# - wordfence
# - better-wp-security (iThemes)
# - all-in-one-wp-security
# - sucuri-scanner
```

**Temporary disable** plugin security:
```bash
wp plugin deactivate wordfence
# atau
wp plugin deactivate better-wp-security
```

Test `/kpi` lagi. Kalau jalan, berarti CSP dari plugin itu.

---

## 🔧 Quick Fix Command (Automated)

Saya akan buatkan fix yang inject CSP header di router secara otomatis.

Jalankan setelah git pull:

```bash
cd ~/mbdcorp.id/wp-content/plugins/kpi-dashboard
git pull
```

---

## 🔍 Diagnostic: Find CSP Source

Check response headers untuk tahu dari mana CSP:

```bash
curl -I https://www.mbdcorp.id/kpi | grep -i "content-security"
```

Atau di browser:
1. F12 → Network tab
2. Refresh halaman
3. Click request ke `/kpi`
4. Tab **Headers**
5. Lihat **Response Headers**
6. Cari `Content-Security-Policy`

Screenshot dan kirim ke saya.

---

## 🎯 Recommended Action

**Paling mudah:**

1. **Cari plugin security aktif:**
   ```bash
   wp plugin list --status=active | grep -E "security|firewall|wordfence"
   ```

2. **Disable sementara:**
   ```bash
   wp plugin deactivate [plugin-name]
   ```

3. **Test /kpi** - kalau jalan, berarti CSP dari plugin itu

4. **Re-enable plugin** dan tambah exception untuk `/kpi` route

---

## 📤 Info Yang Saya Butuhkan

Kirim salah satu:

1. **Screenshot Response Headers** dari browser (F12 → Network → Headers)

2. **Output command:**
   ```bash
   wp plugin list --status=active
   curl -I https://www.mbdcorp.id/kpi
   ```

3. **Nama plugin security** yang aktif di WordPress

Dengan info ini saya bisa kasih solusi spesifik untuk plugin Anda!

---

## ⚠️ Note

`unsafe-eval` memang agak riskan untuk security, tapi:
- React production build **TIDAK pakai eval()**
- Kalau masih butuh unsafe-eval, kemungkinan:
  - Build belum production mode
  - Ada library yang pakai eval()
  - Perlu rebuild dengan config strict

Mari kita cari dulu source CSP-nya, baru fix dengan cara yang tepat.
