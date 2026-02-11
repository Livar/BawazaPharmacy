import { useMemo, useState } from 'react';
import { OcrScanner } from './components/OcrScanner';
import { MedicineForm } from './components/MedicineForm';
import { MedicineList } from './components/MedicineList';
import { useMedicines } from './hooks/useMedicines';
import type { Medicine, MedicineInput, MedicineType } from './types/medicine';

const medicineTypes: Array<MedicineType | 'all'> = [
  'all',
  'tablet',
  'capsule',
  'syrup',
  'injection',
  'cream',
  'drops',
  'other'
];

export default function App() {
  const { items, loading, saving, error, filters, setFilters, create, update, remove } = useMedicines();
  const [editing, setEditing] = useState<Medicine | null>(null);
  const [scannedText, setScannedText] = useState('');

  const selectedFromScan = useMemo(() => {
    if (!scannedText.trim()) {
      return null;
    }

    return items.find(
      (medicine) =>
        scannedText.toLowerCase().includes(medicine.brandName.toLowerCase()) ||
        scannedText.toLowerCase().includes(medicine.genericName.toLowerCase())
    );
  }, [items, scannedText]);

  async function onSubmit(input: MedicineInput) {
    if (editing) {
      await update(editing.id, input);
      setEditing(null);
      return;
    }

    await create(input);
  }

  return (
    <main className="container">
      <header>
        <h1>Bawaza Pharmacy Assistant (PWA)</h1>
        <p>
          Store and find medicines by brand/generic name, dosage, type, and shelf location. Scan packets with OCR for
          faster entry.
        </p>
      </header>

      <section className="panel">
        <h2>Search and filter</h2>
        <div className="search-row">
          <input
            placeholder="Search brand, generic, use, alias, shelf"
            value={filters.query}
            onChange={(event) => setFilters((current) => ({ ...current, query: event.target.value }))}
          />
          <input
            placeholder="Filter by shelf code"
            value={filters.shelfCode}
            onChange={(event) => setFilters((current) => ({ ...current, shelfCode: event.target.value }))}
          />
          <select
            value={filters.type}
            onChange={(event) => setFilters((current) => ({ ...current, type: event.target.value as MedicineType | 'all' }))}
          >
            {medicineTypes.map((type) => (
              <option key={type} value={type}>
                {type}
              </option>
            ))}
          </select>
        </div>
      </section>

      <OcrScanner
        medicines={items}
        onUseDetectedText={(text, matched) => {
          setScannedText(text);
          if (matched) {
            setEditing(matched);
          }
        }}
      />

      {selectedFromScan && (
        <section className="panel success">
          <h3>OCR match found</h3>
          <p>
            We detected <strong>{selectedFromScan.brandName}</strong>. You can now update its shelf/photo details in the
            edit form below.
          </p>
        </section>
      )}

      <MedicineForm initialValue={editing ?? undefined} onSubmit={onSubmit} onCancel={() => setEditing(null)} submitting={saving} />

      <section className="panel">
        <h2>Medicine records</h2>
        {loading && <p>Loading medicines...</p>}
        {error && <p className="error">{error}</p>}
        {!loading && <MedicineList items={items} onEdit={setEditing} onDelete={remove} />}
      </section>
    </main>
  );
}
