# Quick Fix for Blank Page - Upload Instructions

## Cara Upload Pre-Built Files via cPanel

### Langkah 1: Download Pre-Built Files

Minta file `assets-dist-prebuilt.tar.gz` dari developer atau download dari release.

**File size:** ~146 KB (compressed)

### Langkah 2: Upload via cPanel File Manager

1. Login ke **cPanel**
2. Buka **File Manager**
3. Navigate ke:
   ```
   /home/syahiduc/mbdcorp.id/wp-content/plugins/kpi-dashboard/
   ```

4. **Upload** file `assets-dist-prebuilt.tar.gz`

5. **Klik kanan** pada file → **Extract**

6. File akan ter-extract ke folder `assets/dist/`

### Langkah 3: Verifikasi

Setelah extract, struktur folder harus seperti ini:

```
kpi-dashboard/
├── assets/
│   ├── dist/                    ← Folder baru ini
│   │   ├── .vite/
│   │   │   └── manifest.json
│   │   └── assets/
│   │       ├── main-*.js        (450 KB)
│   │       └── main-*.css       (0.2 KB)
│   └── src/
│       └── ...
```

Check di File Manager:
```
assets/dist/.vite/manifest.json          ✅ Harus ada
assets/dist/assets/main-[hash].js        ✅ Harus ada
assets/dist/assets/main-[hash].css       ✅ Harus ada
```

### Langkah 4: Flush Permalinks

**Via WP-CLI:**
```bash
wp rewrite flush
```

**Via WordPress Admin:**
1. Login WordPress Admin
2. Go to **Settings → Permalinks**
3. Click **Save Changes** (tanpa ubah apapun)

### Langkah 5: Test

Akses:
```
https://www.mbdcorp.id/kpi
```

Harusnya muncul **halaman login** sekarang! ✅

---

## Alternative: Upload via FTP

Jika pakai FTP (FileZilla):

1. **Download pre-built files** ke komputer
2. **Extract** di komputer (dapat folder `dist`)
3. **Upload folder** `dist` ke:
   ```
   /wp-content/plugins/kpi-dashboard/assets/dist/
   ```
4. **Flush permalinks** (Settings → Permalinks → Save)
5. **Test** di browser

---

## Troubleshooting

### "Extract gagal di cPanel"

**Solusi:**
1. Extract di komputer lokal
2. Upload folder via FTP

### "Masih blank setelah upload"

**Solusi:**
1. Clear browser cache (Ctrl+Shift+Del)
2. Hard refresh (Ctrl+F5)
3. Check File Manager - pastikan files ada
4. Run: `wp rewrite flush`

### "Permission denied"

**Solusi:**
Set permissions via cPanel:
- Folders: 755
- Files: 644

```bash
chmod -R 755 assets/dist
find assets/dist -type f -exec chmod 644 {} \;
```

---

## Verification Checklist

- [ ] Folder `assets/dist/` exists
- [ ] File `assets/dist/.vite/manifest.json` exists
- [ ] File `assets/dist/assets/main-*.js` exists (450KB)
- [ ] File `assets/dist/assets/main-*.css` exists
- [ ] Permalinks flushed (Settings → Permalinks → Save)
- [ ] Browser cache cleared
- [ ] Access `/kpi` → shows login page

---

## Need the Pre-Built File?

Contact developer atau check repository releases:
- File: `assets-dist-prebuilt.tar.gz`
- Size: ~146 KB
- Contains: Built React app with all dependencies

---

## Manual File Creation (If Extract Fails)

Buat file manually via cPanel File Manager:

### 1. Create `assets/dist/.vite/manifest.json`:
```json
{
  "assets/src/main.tsx": {
    "file": "assets/main-BpyAd86u.js",
    "name": "main",
    "src": "assets/src/main.tsx",
    "isEntry": true,
    "css": [
      "assets/main-BIgbKKtm.css"
    ]
  }
}
```

### 2. Download built files from repository:
- `main-BpyAd86u.js` → upload to `assets/dist/assets/`
- `main-BIgbKKtm.css` → upload to `assets/dist/assets/`

This is tedious - better to use extract method!
