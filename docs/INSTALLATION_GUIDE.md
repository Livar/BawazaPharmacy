# Installation & Deployment Guide (Local PWA)

Audience: IT/admin staff deploying on local infrastructure (XAMPP or IIS + MySQL).

## 1. Deployment models

- **Recommended for small/medium pharmacy**: XAMPP on one local server PC.
- **Recommended for structured IT environments**: IIS + PHP FastCGI + MySQL on Windows Server.

Both keep all data on-premises.

---

## 2. System requirements

### Server
- Windows 10/11 Pro or Windows Server (IIS option)
- PHP 8.1+
- MySQL 8+
- Node.js 20+ (build time only)

### Client devices
- Android Chrome / iOS Safari / desktop Chrome/Edge
- Camera access for barcode scanning

---

## 3. Deploy with XAMPP (step-by-step)

1. Install XAMPP with Apache + MySQL + PHP.
2. Copy project into:
   - `C:\xampp\htdocs\BawazaPharmacy`
3. Create DB schema:
   - open `http://localhost/phpmyadmin`
   - import `docs/mysql_schema.sql`
4. Generate admin password hash:
   ```bash
   php -r "echo password_hash('STRONG_PASSWORD_HERE', PASSWORD_DEFAULT), PHP_EOL;"
   ```
5. Replace `REPLACE_WITH_HASH` in `docs/mysql_schema.sql` and re-run the user insert.
6. Configure DB credentials in `api/config.php`.
7. Build frontend:
   ```bash
   npm install
   npm run build
   ```
8. Serve app and API from same host. Ensure `public/uploads` is writable.

---

## 4. Deploy with IIS + MySQL

1. Install IIS and URL Rewrite module.
2. Install PHP and configure FastCGI handler for `.php`.
3. Create IIS site pointing to this repo directory.
4. Configure rules:
   - route `/api/*.php` to PHP handler
   - SPA fallback for frontend paths to `/index.html`
5. Import `docs/mysql_schema.sql` into MySQL.
6. Configure `api/config.php` credentials.
7. Run:
   ```bash
   npm install
   npm run build
   ```
8. Ensure app static content serves from `dist/` and `/api` remains executable.

---

## 5. PWA requirements checklist

For install-to-home-screen behavior:
- App is served over HTTPS (or localhost during test)
- Manifest available at `/manifest.webmanifest`
- Service worker generated and registered
- Mobile browser supports install prompt (Chrome best support)

---

## 6. Security hardening checklist

- Use HTTPS in production LAN.
- Restrict DB user to this schema only.
- Change default admin credentials immediately.
- Rotate credentials quarterly.
- Restrict filesystem permissions:
  - write only to `public/uploads`
  - deny execution inside uploads folder.
- Backup DB nightly and test restore monthly.

---

## 7. Validation tests after deployment

1. Login with admin user.
2. Add medicine with location + barcode.
3. Upload package and location photos.
4. Test alternatives on a medicine with matching generic peers.
5. Test batch scanner:
   - set location code
   - add 2+ barcodes
   - save updates
6. Install PWA from phone browser.
7. Switch network briefly and confirm cached app loads.

---

## 8. Operational maintenance

- Weekly: export DB backup.
- Monthly: clean old unused images from `public/uploads` if needed.
- Monthly: review scan logs and location consistency.
- Quarterly: review shelf labeling standard alignment.
