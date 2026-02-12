# Pharmacy Learning & Stock-Finder App/PWA Plan

## 1) Goal and vision
Build a **personal pharmacy assistant app** (mobile-first PWA) that helps you:
- Quickly search medicine by **brand** or **generic** name.
- Save and review **uses, dosage notes, dosage forms/types** (tablet, syrup, injection, etc.).
- Store exact **location inside the pharmacy** (shelf code/number, zone, drawer).
- Optionally attach a **photo of product position** and/or package.
- Use camera-based **text scanning (OCR)** to auto-capture medicine names from packets and speed up data entry.

This should be optimized for **fast retrieval while standing in front of customers**.

---

## 2) Core user stories
1. **As a beginner**, I can add a medicine with brand and generic names so I can learn and search it later.
2. **As a staff member in a hurry**, I can type a few letters and instantly see matching medicines and shelf location.
3. **As a visual learner**, I can store a location photo for each item and open it when searching.
4. **As someone handling many products**, I can scan package text with camera, then confirm and save item details.
5. **As inventory changes**, I can update shelf location quickly without re-entering everything else.
6. **As a safety-conscious worker**, I can add warnings/notes (e.g., prescription-only, avoid in pregnancy) for personal reference.

---

## 3) MVP scope (first version)
Keep first release small and reliable.

### Required data fields
- `brandName` (required)
- `genericName` (required)
- `uses` (short text)
- `dosage` (text, ex: “500 mg, 1 tab BID”) 
- `type` (tablet/capsule/syrup/injection/cream/drops/etc.)
- `locationCode` (e.g., Shelf B-03)
- `locationPhotoUrl` (optional)
- `packagePhotoUrl` (optional)
- `notes` (optional)
- `createdAt`, `updatedAt`

### MVP features
- Add/edit/delete medicine record.
- Search by brand, generic, and keyword.
- Filter by type and shelf/zone.
- Open item detail with photos.
- Camera OCR scan to suggest brand/generic name before saving.
- Offline-capable PWA (cached app shell + local database sync when online).

---

## 4) Suggested architecture (simple and scalable)

## Option A (recommended): Firebase-first stack
Good for beginners because backend setup is easier.

- **Frontend/PWA**: React + Vite + Tailwind (or plain CSS)
- **Auth**: Firebase Auth (email/password or Google)
- **Database**: Firestore
- **Image storage**: Firebase Storage
- **OCR**: On-device via browser + Tesseract.js (initial), then optional cloud OCR
- **Hosting**: Firebase Hosting or Vercel

Pros:
- Fast to launch, less backend code.
- Real-time sync and easy image upload.
- Works well for a single user and can later support team accounts.

## Option B: Supabase stack
Also beginner-friendly and SQL-based.

- React PWA + Supabase Auth + Postgres + Storage + Edge Functions.

Pick one. If you’re new-new, **Firebase is the easiest start**.

---

## 5) Data model (practical)

### Collection/Table: `medicines`
- `id`
- `brandName` (indexed)
- `genericName` (indexed)
- `aliases` (array for alternate spellings)
- `uses`
- `dosage`
- `type`
- `location` object:
  - `zone` (optional)
  - `shelf` (required for location tracking)
  - `drawer` (optional)
  - `bin` (optional)
- `locationPhotoUrl`
- `packagePhotoUrl`
- `ocrTextRaw` (for future matching improvements)
- `notes`
- `tags` (e.g., antibiotic, antihistamine)
- `lastSeenAt`
- `createdAt`, `updatedAt`

### Collection/Table: `scan_history` (optional)
- `id`
- `capturedText`
- `matchedMedicineId` (nullable)
- `createdAt`

---

## 6) OCR/camera workflow (important)

### First implementation (simple)
1. Open camera view in app.
2. Capture image (or live frame every few seconds).
3. Run OCR locally.
4. Extract candidate names.
5. Fuzzy match against existing brand/generic names.
6. Show top 3 matches.
7. User confirms the right medicine.
8. User taps “Save location” and enters shelf code / adds shelf photo.

### Accuracy tips
- Force high contrast and crop package label area.
- Add manual correction step (never auto-save without confirmation).
- Save corrected text to improve future matching.

### Future upgrade
- Use barcode/QR scan first (if available), OCR as fallback.
- Optional cloud OCR (Google Vision) for better recognition.

---

## 7) UX flow (day-to-day use)

### Home
- Big search bar
- Buttons: `Scan`, `Add Medicine`, `Recent`, `By Shelf`

### Add medicine
- Form with minimal required fields first.
- Optional advanced section for notes/tags/photos.

### Scan screen
- Live camera + “Capture” button.
- OCR result preview.
- “Match existing” or “Create new medicine”.

### Medicine detail
- Brand + generic at top.
- Uses/dosage/type.
- Location card with shelf code and location photo.
- Edit location quickly button.

### Shelf view
- List by zone/shelf so you can reorganize physically and digitally.

---

## 8) Safety and legal boundaries
This app should be a **learning and location tool**, not a prescribing engine.

Include safety guardrails:
- Banner: “For internal reference only. Verify dosage with licensed pharmacist and official references.”
- Avoid decision-making automation for contraindications/interactions unless data source is verified.
- Log source of any clinical note if you add it.

---

## 9) Security and privacy
Even for personal use:
- Require login.
- Restrict database/storage rules to your account.
- Encrypt transport (HTTPS default).
- Keep backups/export (CSV/JSON).
- No customer personally identifiable info in this app.

---

## 10) Phased implementation roadmap

## Phase 0 (1–2 days): setup
- Create project, auth, DB, storage.
- Define schema and indexes.
- Build base PWA shell and install prompt.

## Phase 1 (3–5 days): core CRUD + search
- Medicine form and detail pages.
- Fast search and filters.
- Shelf/location fields.

## Phase 2 (3–5 days): photos + offline
- Upload package/location photos.
- Offline cache + local writes + sync.

## Phase 3 (4–7 days): OCR scan
- Camera capture.
- OCR extraction + fuzzy matching.
- Confirm flow and save location quickly.

## Phase 4 (ongoing): quality upgrades
- Barcode support.
- Bulk import from CSV.
- Shelf map visualization.
- Team sharing mode.

---

## 11) Minimum technical backlog
- [ ] Project bootstrap (React PWA)
- [ ] Auth screens
- [ ] Firestore schema + security rules
- [ ] Add/Edit medicine form
- [ ] Search index + fuzzy match function
- [ ] Image upload component
- [ ] OCR service wrapper
- [ ] Scan confirmation modal
- [ ] Shelf/location module
- [ ] Offline sync and conflict handling
- [ ] Export/import tool

---

## 12) Success metrics (so you know it works)
- Search result appears in under 1 second.
- Add new medicine in under 45 seconds.
- Update location in under 10 seconds.
- OCR-assisted entry reduces typing by at least 50%.
- At least 90% of your frequently used medicines have location data.

---

## 13) Starter feature decisions (recommended defaults)
- Use **single account** first.
- Keep dosage as free-text initially (faster build).
- Use simple shelf code format: `Zone-Shelf-Row` (e.g., `A-03-2`).
- Save 1 package photo + 1 shelf photo per medicine initially.
- Add barcode later.

---

## 14) Practical first-week action plan for you
1. Define your shelf code system on paper.
2. Enter top 50 fastest-moving medicines manually.
3. Add location photo for only confusing shelves first.
4. Turn on OCR and use it for new items.
5. End of week: check which searches failed and add aliases.

---

## 15) Nice-to-have ideas after MVP
- Voice search ("where is Augmentin?")
- Multi-language names
- Low-stock reminders
- Expiry tracking
- Printable shelf labels with QR linking to app records

---

## Final recommendation
Start with a **lean PWA + Firebase + OCR assist + manual confirmation**. This gives you a practical, fast, and realistic tool you can use at work quickly, while leaving room to grow into inventory and advanced pharmacy workflows later.
