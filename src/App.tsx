import { useMemo, useState } from 'react';
import { AuthPanel } from './components/AuthPanel';
import { OcrScanner } from './components/OcrScanner';
import { MedicineForm } from './components/MedicineForm';
import { MedicineList } from './components/MedicineList';
import { ShelfMap } from './components/ShelfMap';
import { useAuth } from './hooks/useAuth';
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

function csvEscape(value: string) {
  return `"${value.replace(/"/g, '""')}"`;
}

function toCsvLine(values: string[]) {
  return values.map(csvEscape).join(',');
}

export default function App() {
  const { user, loading: authLoading } = useAuth();
  const { allItems, items, loading, saving, error, filters, setFilters, create, createBulk, update, remove } = useMedicines(Boolean(user));
  const [editing, setEditing] = useState<Medicine | null>(null);
  const [scannedText, setScannedText] = useState('');

  const selectedFromScan = useMemo(() => {
    if (!scannedText.trim()) {
      return null;
    }

    return allItems.find(
      (medicine) =>
        scannedText.toLowerCase().includes(medicine.brandName.toLowerCase()) ||
        scannedText.toLowerCase().includes(medicine.genericName.toLowerCase()) ||
        scannedText.includes(medicine.barcode || '')
    );
  }, [allItems, scannedText]);

  async function onSubmit(input: MedicineInput) {
    if (editing) {
      await update(editing.id, input);
      setEditing(null);
      return;
    }

    await create(input);
  }

  function exportCsv() {
    const headers = [
      'brandName',
      'genericName',
      'uses',
      'dosage',
      'type',
      'zone',
      'shelfCode',
      'barcode',
      'requiresPrescription',
      'aliases',
      'notes'
    ];

    const lines = [toCsvLine(headers)];

    for (const item of allItems) {
      lines.push(
        toCsvLine([
          item.brandName,
          item.genericName,
          item.uses,
          item.dosage,
          item.type,
          item.zone,
          item.shelfCode,
          item.barcode || '',
          String(item.requiresPrescription),
          item.aliases.join('|'),
          item.notes
        ])
      );
    }

    const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `medicines-${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    URL.revokeObjectURL(url);
  }

  async function importCsv(file: File) {
    const text = await file.text();
    const lines = text.split(/\r?\n/).filter(Boolean);
    if (lines.length < 2) {
      return;
    }

    const rows = lines.slice(1);
    const imported: MedicineInput[] = rows.map((line) => {
      const cols = line
        .split(',')
        .map((column) => column.replace(/^"|"$/g, '').replace(/""/g, '"'));

      return {
        brandName: cols[0] || '',
        genericName: cols[1] || '',
        uses: cols[2] || '',
        dosage: cols[3] || '',
        type: (cols[4] as MedicineType) || 'other',
        zone: cols[5] || '',
        shelfCode: cols[6] || '',
        barcode: cols[7] || '',
        requiresPrescription: cols[8] === 'true',
        aliases: (cols[9] || '').split('|').filter(Boolean),
        notes: cols[10] || '',
        locationPhotoUrl: '',
        packagePhotoUrl: '',
        ocrTextRaw: ''
      };
    });

    await createBulk(imported.filter((item) => item.brandName && item.genericName && item.shelfCode));
  }

  return (
    <main className="container">
      <header>
        <h1>Bawaza Pharmacy Assistant (PWA)</h1>
        <p>
          Store and find medicines by brand/generic name, dosage, type, and shelf location. Scan packets with barcode or
          OCR for faster entry.
        </p>
      </header>

      {authLoading ? (
        <section className="panel">
          <p>Checking authentication...</p>
        </section>
      ) : (
        <AuthPanel email={user?.email} />
      )}

      {!user ? (
        <section className="panel">
          <p>Please sign in to access your medicine records.</p>
        </section>
      ) : (
        <>
          <section className="panel">
            <h2>Search and filter</h2>
            <div className="search-row">
              <input
                placeholder="Search brand, generic, use, alias, shelf, barcode"
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
                onChange={(event) =>
                  setFilters((current) => ({ ...current, type: event.target.value as MedicineType | 'all' }))
                }
              >
                {medicineTypes.map((type) => (
                  <option key={type} value={type}>
                    {type}
                  </option>
                ))}
              </select>
              <label className="checkbox-row">
                <input
                  type="checkbox"
                  checked={filters.prescriptionOnly}
                  onChange={(event) =>
                    setFilters((current) => ({ ...current, prescriptionOnly: event.target.checked }))
                  }
                />
                Prescription only
              </label>
            </div>
            <div className="button-row">
              <button className="secondary" onClick={exportCsv}>
                Export CSV
              </button>
              <label>
                Import CSV
                <input type="file" accept=".csv,text/csv" onChange={(event) => event.target.files?.[0] && void importCsv(event.target.files[0])} />
              </label>
            </div>
          </section>

          <OcrScanner
            medicines={allItems}
            onUseDetectedText={(text, matched) => {
              setScannedText(text);
              if (matched) {
                setEditing(matched);
              }
            }}
          />

          {selectedFromScan && (
            <section className="panel success">
              <h3>Scan match found</h3>
              <p>
                We detected <strong>{selectedFromScan.brandName}</strong>. You can now update its shelf/photo details in
                the edit form below.
              </p>
            </section>
          )}

          <MedicineForm
            initialValue={editing ?? undefined}
            onSubmit={onSubmit}
            onCancel={() => setEditing(null)}
            submitting={saving}
          />

          <section className="panel">
            <h2>Medicine records</h2>
            {loading && <p>Loading medicines...</p>}
            {error && <p className="error">{error}</p>}
            {!loading && <MedicineList items={items} onEdit={setEditing} onDelete={remove} />}
          </section>

          <ShelfMap items={allItems} />
        </>
      )}
    </main>
  );
}
