# Manager Operations Guide

Audience: Pharmacy manager / supervisor.

## Objective
Use this system to reduce medicine search time at shelves and improve substitute handling.

## 1. Governance setup

1. Assign one app owner (manager or senior pharmacist).
2. Define shelf/location naming standard before data entry.
3. Set user credentials policy for staff who need access.
4. Require location photo for high-confusion shelves.

## 2. Location labeling policy (recommended)

Format: `ZONE-SHELF-ROW-BIN`

- Zone: A, B, C...
- Shelf: 01, 02, 03...
- Row: 1, 2, 3...
- Bin: L, C, R

Example: `B-04-2-R`

## 3. Rollout plan

Week 1:
- Label all shelves physically.
- Enter top 100 most requested medicines.
- Add photos for the 20 hardest shelves.

Week 2:
- Batch-scan by location shelf-by-shelf.
- Validate alternatives coverage for core generics.

Week 3:
- Audit search failures and correct naming/aliases/barcodes.

## 4. Daily manager checks

- Verify newly added medicines include location code.
- Check that substitutes are represented for high-demand drugs.
- Review scan logs (spot-check) after shelf reorganizations.

## 5. KPI suggestions

- Average time to locate medicine (target < 20 sec).
- % of fast-moving medicines with barcode + location + photo.
- % of high-demand generics with at least one alternative entry.
- Number of search misses per shift.

## 6. Staff SOP summary

- Always search app before asking shelf support.
- If brand unavailable, use Alternatives immediately.
- After shelf movement, run location-first barcode batch update.
- Do not use app for quantity stock counting.

## 7. Risk controls

- Never store customer personal data.
- Keep login shared only with authorized staff.
- Record prescription-warning medicines accurately.
- Backup database regularly.
