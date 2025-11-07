# Cara Mengatasi Blank White Screen di /kpi

Blank white screen biasanya terjadi karena **frontend belum di-build** atau ada error di JavaScript.

---

## 🔍 Langkah 1: Jalankan Debug Script

Akses URL ini di browser:

```
http://yoursite.com/wp-content/plugins/kpi-dashboard/debug-frontend.php
```

Script ini akan memberitahu Anda:
- ✅ Apakah frontend sudah di-build
- ✅ File mana yang hilang
- ✅ Solusi langsung

**Screenshot hasilnya dan kirim ke saya!**

---

## 🛠️ Langkah 2: Build Frontend (Kemungkinan Besar Ini Masalahnya)

Penyebab paling umum adalah **frontend belum di-build**.

### Via SSH/Terminal:

```bash
# Masuk ke directory plugin
cd /home/syahiduc/mbdcorp.id/wp-content/plugins/kpi-dashboard

# Install dependencies (jika belum)
npm install --legacy-peer-deps

# Build untuk production
npm run build

# Verifikasi build berhasil
ls -la assets/dist/assets/
```

Jika berhasil, Anda akan melihat:
```
main-[hash].js    (sekitar 450KB)
main-[hash].css   (sekitar 0.2KB)
```

### Via cPanel File Manager:

1. Download folder `kpi-dashboard` dari server
2. Di komputer lokal, jalankan:
   ```bash
   cd kpi-dashboard
   npm install --legacy-peer-deps
   npm run build
   ```
3. Upload folder `assets/dist/` kembali ke server

---

## 🌐 Langkah 3: Check Browser Console

Jika sudah build tapi masih blank:

1. Buka halaman `/kpi` di browser
2. Tekan **F12** (atau klik kanan → Inspect)
3. Pilih tab **Console**
4. Refresh halaman
5. **Screenshot semua error merah** dan kirim ke saya

Biasanya error yang muncul:
- ❌ `Failed to load module` → Frontend belum di-build
- ❌ `404 Not Found` pada file JS → Path salah atau file tidak ada
- ❌ `SyntaxError` → Build corrupted, perlu rebuild

---

## 🔄 Langkah 4: Flush Permalinks

Kadang WordPress perlu refresh URL rules:

```bash
wp rewrite flush
```

Atau via WordPress Admin:
1. Go to **Settings → Permalinks**
2. Klik **Save Changes** (tanpa ubah apapun)

---

## ✅ Verifikasi Fix

Setelah build, cek:

1. **File exists:**
   ```bash
   ls -la wp-content/plugins/kpi-dashboard/assets/dist/assets/
   ```
   Harus ada `main-*.js` dan `main-*.css`

2. **Access /kpi:**
   ```
   https://yourdomain.com/kpi
   ```
   Harus muncul halaman login

3. **No errors:**
   Check browser console (F12) - tidak ada error merah

---

## 🚨 Troubleshooting

### Problem: "npm: command not found"

**Solusi:** Install Node.js di server

```bash
# Ubuntu/Debian
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verify
node -v
npm -v
```

### Problem: "EACCES: permission denied"

**Solusi:** Fix permissions

```bash
sudo chown -R $USER:$USER /home/syahiduc/mbdcorp.id/wp-content/plugins/kpi-dashboard
```

### Problem: "npm ERR! peer dependencies"

**Solusi:** Gunakan flag `--legacy-peer-deps`

```bash
npm install --legacy-peer-deps
```

### Problem: Masih blank setelah build

**Solusi:** Clear cache

1. Clear browser cache (Ctrl+Shift+Delete)
2. Clear WordPress cache jika pakai plugin caching
3. Hard refresh (Ctrl+F5)

---

## 📦 Alternative: Upload Pre-Built Files

Jika tidak bisa build di server, saya bisa kirimkan folder `assets/dist` yang sudah di-build untuk Anda upload manual.

---

## 💡 Quick Diagnostic

**Jalankan ini dan kirim hasilnya:**

```bash
# Check if files exist
ls -la wp-content/plugins/kpi-dashboard/assets/dist/

# Check manifest
cat wp-content/plugins/kpi-dashboard/assets/dist/.vite/manifest.json

# Check Node version (if available)
node -v
npm -v
```

---

## 🆘 Butuh Bantuan?

Kirim informasi berikut:

1. ✅ Screenshot dari **debug-frontend.php**
2. ✅ Screenshot browser console (F12 → Console tab)
3. ✅ Output dari: `ls -la assets/dist/`
4. ✅ Server type (shared hosting / VPS / dedicated)
5. ✅ Akses SSH tersedia? (ya/tidak)

Dengan info ini saya bisa bantu langsung! 🚀

---

## 📝 Kemungkinan Besar Solusinya:

**90% kasus:** Frontend belum di-build

**Solusi:**
```bash
cd kpi-dashboard
npm install --legacy-peer-deps
npm run build
```

**5% kasus:** JavaScript error

**Solusi:** Check console, kirim error ke saya

**5% kasus:** Permalink issue

**Solusi:** `wp rewrite flush`
