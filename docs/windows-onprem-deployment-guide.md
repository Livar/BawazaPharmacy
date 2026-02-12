# Bawaza Pharmacy Assistant - Windows On-Prem Server Deployment Guide

This guide explains how to run the app **inside your pharmacy** with **no external hosting**.

> Important: the current app code uses Firebase services by default.
> If you want fully local/offline infrastructure, follow the **Option B (IIS + Node.js API + MySQL)** plan below.

---

## 1) Choose your deployment mode

## Option A - Fastest (IIS serves PWA, Firebase stays external)
- Good if you want to go live quickly.
- App files are hosted on your pharmacy Windows server (IIS).
- Data still uses Firebase cloud.

## Option B - Full on-prem (recommended for your requirement)
- IIS serves PWA on your server.
- Local API server runs on same Windows machine.
- MySQL database runs locally (MySQL Server/XAMPP MariaDB).
- No external hosting required.

---

## 2) Server prerequisites (for both options)

- Windows Server 2019/2022 (or Windows 10/11 Pro for pilot).
- Static IP on pharmacy LAN (example: `192.168.1.20`).
- DNS or local host name (example: `pharmacy-app.local`).
- TLS certificate for HTTPS (recommended).
- Open firewall ports:
  - 80 (HTTP)
  - 443 (HTTPS)
  - 3306 (MySQL only if remote DB access needed)

---

## 3) IIS setup (common)

1. Open **Server Manager** → **Add Roles and Features**.
2. Install **Web Server (IIS)**.
3. Under IIS features, enable:
   - Static Content
   - Default Document
   - URL Rewrite (install separately via Web Platform Installer/MSI if missing)
4. (Optional for API reverse proxy) Install **Application Request Routing (ARR)**.

---

## 4) Build and publish frontend to IIS

From your project folder:

```bash
npm install
npm run build
```

Then copy all files from `dist/` to IIS site root, e.g.:
- `C:\inetpub\pharmacy-app\`

In IIS:
1. Create new site: **PharmacyAssistant**.
2. Physical path: `C:\inetpub\pharmacy-app`.
3. Binding: `http` on port `80` (or `https` on `443`).
4. Start site.

Use the `deploy/iis/web.config` in this repo (copy into site root) so React routes/PWA work.

---

## 5) Option A details (IIS + Firebase)

If using Firebase data:
1. Keep current `.env` values (`VITE_FIREBASE_*`).
2. Build again and copy fresh `dist` to IIS.
3. Ensure pharmacy internet access allows Firebase endpoints.

This is not fully on-prem data, but easiest initial rollout.

---

## 6) Option B full on-prem architecture (IIS + Node API + MySQL)

## 6.1 Install MySQL (or XAMPP MariaDB)

### MySQL Community Server
1. Install MySQL Server 8.x.
2. Set root password.
3. Create database and app user:

```sql
CREATE DATABASE pharmacy_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pharmacy_user'@'%' IDENTIFIED BY 'StrongPasswordHere!';
GRANT ALL PRIVILEGES ON pharmacy_app.* TO 'pharmacy_user'@'%';
FLUSH PRIVILEGES;
```

### Alternative: XAMPP
- Install XAMPP and use MariaDB service.
- Create same DB/user via phpMyAdmin.

## 6.2 Run local backend API

Recommended stack:
- Node.js LTS
- Express or NestJS
- Prisma or Sequelize
- JWT auth

Suggested API modules:
- `/auth/login`
- `/auth/register`
- `/medicines` CRUD
- `/upload` for package/shelf photos (local folder storage)

Suggested server folder:
- `C:\pharmacy-api\`

Run with PM2 (or NSSM service):

```bash
npm install -g pm2
pm2 start dist/server.js --name pharmacy-api
pm2 save
pm2 startup
```

## 6.3 Reverse proxy API from IIS

Use IIS ARR + URL Rewrite to forward:
- `/api/*` → `http://127.0.0.1:3001/*`

Then your phones use one URL:
- `https://pharmacy-app.local/`

Frontend static files and API both served through IIS.

## 6.4 Update frontend env for local API

When moving off Firebase, use `.env` like:

```env
VITE_API_BASE_URL=/api
```

Then rebuild and republish `dist/`.

---

## 7) Local file storage for medicine photos

Use folder:
- `C:\pharmacy-data\images\`

Best practices:
- Daily backup to external disk/NAS.
- Weekly backup copy off-machine.
- Restrict NTFS permissions to service account only.

---

## 8) Staff onboarding and access

- Give staff URL: `https://pharmacy-app.local/`.
- Create individual accounts for each staff member.
- Each staff member logs in and can search/add/update medicine location.
- For training, prepare first 100 high-frequency medicines.

---

## 9) Security checklist

- Use HTTPS with internal trusted certificate.
- Force strong passwords.
- Disable anonymous DB remote access if unnecessary.
- Daily automated DB backup (`mysqldump`).
- Restrict server RDP to admin subnet only.
- Keep Windows, Node, and DB patched.

---

## 10) Backup and restore runbook

## Backup script (daily task scheduler)

```bat
@echo off
set TS=%date:~-4,4%-%date:~-10,2%-%date:~-7,2%
"C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe" -u pharmacy_user -pStrongPasswordHere! pharmacy_app > C:\pharmacy-backups\pharmacy_app_%TS%.sql
```

Also copy:
- `C:\pharmacy-data\images\`

## Restore

```bat
"C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -u pharmacy_user -pStrongPasswordHere! pharmacy_app < C:\pharmacy-backups\pharmacy_app_YYYY-MM-DD.sql
```

---

## 11) Go-live checklist

- [ ] IIS site reachable from staff phones on Wi-Fi.
- [ ] Login works for at least 2 test users.
- [ ] Add/edit/delete medicine works.
- [ ] Scan workflow works on Android + iPhone.
- [ ] Prescription warning toggle works.
- [ ] Backup job tested and restore tested.
- [ ] UPS connected to server.

---

## 12) Recommended rollout sequence

1. Pilot with Option A (1-3 days).
2. Build local API + MySQL (Option B).
3. Migrate medicine data from Firebase CSV export/import.
4. Switch frontend to local `/api`.
5. Finalize fully on-prem mode.
