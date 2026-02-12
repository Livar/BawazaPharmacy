# Pharmacy Medicine Locator (Local + PWA)

A local-first medicine location system for pharmacies, optimized for phones but fully usable on desktop/laptop.

## Core purpose
- Find medicines quickly in-store by brand, generic, or barcode.
- Show alternatives (same generic) with exact location and photos.
- Speed up shelf changes with location-first batch barcode scanning.

> Not intended for quantity stock management, billing, or sales.

---

## Architecture
- Frontend: React + Vite
- PWA: `vite-plugin-pwa` + service worker + installable manifest
- Backend: PHP API (`/api`)
- Database: MySQL
- Hosting: XAMPP or IIS + PHP + MySQL (on-prem)

---

## Documentation map

### For IT/Admin
- Full install/deploy guide: `docs/INSTALLATION_GUIDE.md`
- MySQL schema: `docs/mysql_schema.sql`

### For managers
- Rollout and governance guide: `docs/MANAGER_OPERATIONS_GUIDE.md`

### For staff
- Phone-first usage guide: `docs/STAFF_QUICKSTART_GUIDE.md`

---

## Key workflows

1. **Search & locate**
   - Search by brand/generic/barcode
   - Read exact location code and photo

2. **Alternatives**
   - Open a medicine and press **Alternatives**
   - See same-generic substitutes and exact locations/photos

3. **Location-first batch scanning**
   - Enter target location code once
   - Scan many barcodes in sequence
   - Save all scanned medicines to that location in one operation

4. **Prescription warning**
   - Mark medicines that require prescription
   - Filter by prescription-only list

---

## Quick start (developer/local test)

```bash
npm install
npm run build
npm run dev
```

Backend/API requires PHP + MySQL configured per installation guide.

---

## Security baseline implemented
- Session-auth protected API endpoints
- PDO prepared statements
- MIME-validated image uploads
- User-scoped medicine records
- Upload storage isolated to `public/uploads`

---

## PWA notes
- Installable on Android/iOS/desktop browsers.
- Works best over HTTPS in production (or localhost for testing).
- Service worker caches shell + image assets for improved mobile behavior.
