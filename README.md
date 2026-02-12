# Bawaza Pharmacy Assistant PWA

A mobile-first pharmacy helper app for storing medicine knowledge and exact in-store locations.

## Implemented features

- React + TypeScript + Vite installable PWA.
- Firebase Auth UI (email/password signup + login + logout).
- Firestore + Storage integration for medicine records and image uploads.
- Offline Firestore persistence (IndexedDB) for cached reads/writes.
- Add / edit / delete medicine entries.
- Search by brand/generic/use/aliases/barcode and filter by type/shelf.
- Prescription warning toggle per medicine (`requiresPrescription`).
- Prescription-only filter in search.
- Upload package photo and shelf/location photo.
- Barcode-first scan flow with OCR fallback (`tesseract.js`).
- CSV export/import for backup or bulk loading.
- Shelf map section grouped by zone/shelf.


## On-prem Windows Server guides

If you want to host inside the pharmacy (no external hosting), use these:

- `docs/windows-onprem-deployment-guide.md`
- `docs/staff-phone-install-and-usage-guide.md`
- IIS SPA config template: `deploy/iis/web.config`

## 1) Local setup

### Prerequisites
- Node.js 20+
- npm 10+
- Firebase project

### Install and run
```bash
npm install
cp .env.example .env
npm run dev
```

Fill `.env` from Firebase web app config:

```env
VITE_FIREBASE_API_KEY=
VITE_FIREBASE_AUTH_DOMAIN=
VITE_FIREBASE_PROJECT_ID=
VITE_FIREBASE_STORAGE_BUCKET=
VITE_FIREBASE_MESSAGING_SENDER_ID=
VITE_FIREBASE_APP_ID=
```

## 2) Firebase setup

### A. Create project + web app
1. Open Firebase Console.
2. Create project (e.g., `bawaza-pharmacy`).
3. Add Web App.
4. Copy config into `.env`.

### B. Enable Authentication
1. Open **Authentication** > **Sign-in method**.
2. Enable **Email/Password** provider.

### C. Enable Firestore
1. Open **Firestore Database**.
2. Start in production mode.
3. Create collection `medicines`.

### D. Enable Storage
1. Open **Storage**.
2. Create bucket.

### E. Security rules (owner-only)

#### Firestore rules
```txt
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    match /medicines/{docId} {
      allow read, write: if request.auth != null && request.auth.uid == resource.data.ownerId;
      allow create: if request.auth != null && request.resource.data.ownerId == request.auth.uid;
    }
  }
}
```

#### Storage rules
```txt
rules_version = '2';
service firebase.storage {
  match /b/{bucket}/o {
    match /medicine-images/{allPaths=**} {
      allow read, write: if request.auth != null;
    }
  }
}
```

## 3) Deploy to Firebase Hosting

### Install CLI and initialize
```bash
npm install -g firebase-tools
firebase login
firebase init
```

Choose:
- Hosting
- Public directory: `dist`
- Single-page app rewrites: `Yes`

### Build and deploy
```bash
npm run build
firebase deploy
```

## 4) Deploy to Vercel (alternative)

1. Push to GitHub.
2. Import repo in Vercel.
3. Framework: **Vite**.
4. Add all `VITE_FIREBASE_*` variables.
5. Deploy.

## 5) Usage flow

1. Sign up/sign in.
2. Add medicines with brand/generic names, dosage, type, shelf, barcode.
3. Toggle **Requires prescription warning** for restricted medicines.
4. During work, search by name or barcode.
5. Use scanner: barcode detection first; OCR fallback if barcode is unavailable.
6. Use **Prescription only** filter when needed.
7. Export CSV periodically for backup.

## 6) Troubleshooting

- **No data visible**: verify Firestore rules and that you are logged in.
- **Image upload fails**: check Storage rules.
- **Scanner not detecting barcode**: use clearer lighting; barcode support varies by browser.
- **OCR weak results**: crop close to medicine name and increase contrast.
