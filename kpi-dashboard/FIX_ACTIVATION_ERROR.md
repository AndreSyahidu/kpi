# Cara Mengatasi Fatal Error saat Aktivasi Plugin

## Langkah 1: Lihat Error Detail

Ada 2 cara untuk melihat error detail:

### Cara A: Gunakan Debug Script (Recommended)

1. Akses URL berikut di browser Anda:
   ```
   http://yoursite.com/wp-content/plugins/kpi-dashboard/debug-activation.php
   ```

2. Script akan menampilkan:
   - Versi PHP dan WordPress
   - Status PHP extensions
   - File yang ada/tidak ada
   - Error detail dengan stack trace
   - Status database tables

3. Screenshot hasilnya dan kirim ke saya

### Cara B: Aktifkan WordPress Debug Mode

1. Edit file `wp-config.php`
2. Tambahkan sebelum baris `/* That's all, stop editing! */`:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   @ini_set('display_errors', 0);
   ```

3. Coba aktivasi plugin lagi
4. Buka file `wp-content/debug.log`
5. Lihat error terakhir dan kirim ke saya

---

## Langkah 2: Quick Fix (Jika error masih belum jelas)

Saya sudah menyiapkan perbaikan untuk masalah umum:

### Fix 1: Pastikan PHP Extensions Tersedia

Jalankan command ini untuk cek:
```bash
php -m | grep -E 'mysqli|mbstring|json|openssl'
```

Jika ada yang tidak muncul, install:
```bash
# Ubuntu/Debian
sudo apt-get install php8.1-mysql php8.1-mbstring php8.1-json

# CentOS/RHEL
sudo yum install php-mysqlnd php-mbstring php-json
```

### Fix 2: Manual Database Installation

Jika masalah di database, install manual via WP-CLI:

```bash
cd wp-content/plugins/kpi-dashboard
wp eval-file debug-activation.php
```

---

## Langkah 3: Perbaikan Sementara

Jika Anda perlu aktivasi cepat, gunakan versi safe mode:

Saya akan buatkan versi activator yang lebih aman dengan error handling lengkap.

---

## Error Umum dan Solusi

### Error: "Cannot use object of type WP_Error as array"
**Solusi:** Database permission issue. Pastikan user MySQL punya CREATE TABLE permission.

### Error: "Call to undefined function password_hash()"
**Solusi:** Upgrade PHP ke 8.1+

### Error: "Table already exists"
**Solusi:** Drop tables lalu aktivasi ulang:
```bash
wp kpi-dashboard reset --yes
```

### Error: "Maximum execution time exceeded"
**Solusi:** Naikkan time limit di php.ini:
```ini
max_execution_time = 300
```

---

## Butuh Bantuan Langsung?

Kirim informasi berikut:

1. ✅ Screenshot dari debug-activation.php
2. ✅ Atau isi file wp-content/debug.log (bagian error terakhir)
3. ✅ Versi PHP: `php -v`
4. ✅ Versi WordPress
5. ✅ Server type (Apache/Nginx)

Dengan informasi ini saya bisa perbaiki dengan cepat!
