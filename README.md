# Bawaza Pharmacy Assistant PWA

A production-ready starter for your pharmacy learning/location system.

## What is implemented now

- Mobile-first PWA built with React + TypeScript + Vite.
- Firebase integration (Firestore + Storage).
- Add / edit / delete medicine entries.
- Search by brand/generic/use/aliases and filter by type/shelf.
- Store usage, dosage, type, notes, zone + shelf code.
- Upload package photo and shelf/location photo.
- OCR packet scanning (camera capture on mobile) via `tesseract.js`.
- OCR-assisted matching flow to jump directly into editing matched medicine location.

## 1) Local setup

### Prerequisites
- Node.js 20+
- npm 10+
- Firebase project

### Install
```bash
npm install
cp .env.example .env
```

Fill `.env` with your Firebase web app config values.

### Run locally
```bash
npm run dev
```
Open the shown localhost URL.

## 2) Firebase setup (required)

### Step A: Create project + web app
1. Go to Firebase Console.
2. Create a project (example: `bawaza-pharmacy`).
3. Add a web app and copy config keys into `.env`.

### Step B: Enable Firestore
1. Open Firestore Database.
2. Start in production mode.
3. Create collection: `medicines`.

### Step C: Enable Storage
1. Open Storage.
2. Create bucket.

### Step D: Security rules (single-user quick start)
Use rules that only allow your account. Replace `YOUR_UID` with your Firebase Auth user UID.

#### Firestore rules
```txt
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    match /medicines/{docId} {
      allow read, write: if request.auth != null && request.auth.uid == 'YOUR_UID';
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
      allow read, write: if request.auth != null && request.auth.uid == 'YOUR_UID';
    }
  }
}
```

> Note: the app code currently initializes Auth service but does not yet include login screen UI. For immediate private use, deploy behind private access or add Firebase Email/Password auth UI next.

## 3) Deploy to Firebase Hosting

### Install Firebase CLI
```bash
npm install -g firebase-tools
firebase login
firebase init
```

When prompted:
- Select **Hosting**.
- Public directory: `dist`
- Configure as single-page app: `Yes`
- GitHub action: optional

### Build + deploy
```bash
npm run build
firebase deploy
```

Your deployed URL appears in the terminal after deploy.

## 4) Deploy to Vercel (alternative)

1. Push this repo to GitHub.
2. Import project in Vercel.
3. Framework preset: **Vite**.
4. Add all `VITE_FIREBASE_*` env vars in Vercel project settings.
5. Deploy.

## 5) How to use day-to-day

1. Add medicines with brand + generic + dosage + shelf code.
2. Use search bar during customer interactions.
3. Capture shelf photo for tricky shelves.
4. Use OCR scanner to capture text from packet labels.
5. If OCR matches existing medicine, update shelf/location quickly.

## 6) Recommended immediate next improvements

1. Add login UI (Firebase Email/Password).
2. Add offline Firestore persistence.
3. Add barcode scanning first, OCR fallback.
4. Add export/import CSV.
5. Add shelf map view.

## 7) Troubleshooting

- **Blank screen**: check `.env` keys and restart `npm run dev`.
- **Upload fails**: verify Storage rules and bucket path.
- **No medicine list**: verify Firestore `medicines` collection permissions.
- **OCR inaccurate**: use clearer, close-up photo with high contrast.
