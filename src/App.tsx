import { type FormEvent, useEffect, useMemo, useState } from 'react';
import {
  alternativesFor,
  authLogin,
  authLogout,
  authMe,
  batchUpdateLocation,
  deleteMedicine,
  listMedicines,
  saveMedicine,
  uploadImage
} from './services/api';
import type { Medicine, MedicineType, User } from './types/medicine';

const types: Array<MedicineType | 'all'> = ['all', 'tablet', 'capsule', 'syrup', 'injection', 'cream', 'drops', 'other'];


declare global {
  interface Window {
    BarcodeDetector?: new () => { detect: (v: HTMLVideoElement) => Promise<Array<{ rawValue?: string }>> };
  }
}

const emptyMedicine: Partial<Medicine> = {
  brand_name: '',
  generic_name: '',
  uses_text: '',
  dosage: '',
  type: 'tablet',
  location_code: '',
  barcode: '',
  notes: '',
  requires_prescription: 0,
  package_photo_url: '',
  location_photo_url: ''
};

export default function App() {
  const [user, setUser] = useState<User | null>(null);
  const [username, setUsername] = useState('admin');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const [q, setQ] = useState('');
  const [locationFilter, setLocationFilter] = useState('');
  const [typeFilter, setTypeFilter] = useState<MedicineType | 'all'>('all');
  const [prescriptionOnly, setPrescriptionOnly] = useState(false);

  const [items, setItems] = useState<Medicine[]>([]);
  const [editing, setEditing] = useState<Partial<Medicine>>(emptyMedicine);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [alternatives, setAlternatives] = useState<Medicine[]>([]);
  const [selectedAlternativeOf, setSelectedAlternativeOf] = useState<string>('');

  const [scanLocation, setScanLocation] = useState('');
  const [scanQueue, setScanQueue] = useState<string[]>([]);
  const [scannerRunning, setScannerRunning] = useState(false);

  useEffect(() => {
    void init();
  }, []);

  useEffect(() => {
    if (user) {
      void loadMedicines();
    }
  }, [user, q, locationFilter, typeFilter, prescriptionOnly]);

  async function init() {
    try {
      const me = await authMe();
      setUser(me);
    } catch {
      setUser(null);
    } finally {
      setLoading(false);
    }
  }

  async function loadMedicines() {
    const list = await listMedicines({ q, location: locationFilter, type: typeFilter, prescriptionOnly });
    setItems(list);
  }

  async function handleLogin(e: FormEvent) {
    e.preventDefault();
    setError('');
    try {
      const logged = await authLogin(username, password);
      setUser(logged);
      setPassword('');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Login failed');
    }
  }

  async function handleLogout() {
    await authLogout();
    setUser(null);
    setItems([]);
  }

  async function handleSaveMedicine(e: React.FormEvent) {
    e.preventDefault();
    setError('');
    try {
      await saveMedicine({ ...editing, id: editingId ?? undefined });
      setEditing(emptyMedicine);
      setEditingId(null);
      await loadMedicines();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to save medicine');
    }
  }

  function beginEdit(item: Medicine) {
    setEditing(item);
    setEditingId(item.id);
  }

  async function remove(id: number) {
    await deleteMedicine(id);
    await loadMedicines();
  }

  async function showAlternatives(item: Medicine) {
    const list = await alternativesFor(item.id);
    setAlternatives(list);
    setSelectedAlternativeOf(item.brand_name);
  }

  async function queueFromManualBarcode(barcode: string) {
    const clean = barcode.trim();
    if (!clean) return;
    setScanQueue((prev) => (prev.includes(clean) ? prev : [...prev, clean]));
  }

  useEffect(() => {
    if (!scannerRunning) return;
    let stream: MediaStream | null = null;
    let timer = 0;
    const video = document.getElementById('batch-video') as HTMLVideoElement | null;
    if (!video || !('BarcodeDetector' in window)) return;

    const Detector = window.BarcodeDetector;
    if (!Detector) return;
    const detector = new Detector();

    navigator.mediaDevices
      .getUserMedia({ video: { facingMode: 'environment' } })
      .then((s) => {
        stream = s;
        video.srcObject = s;
        void video.play();
        timer = window.setInterval(async () => {
          const codes = await detector.detect(video);
          const raw = codes[0]?.rawValue?.trim();
          if (raw) {
            setScanQueue((prev) => (prev.includes(raw) ? prev : [...prev, raw]));
          }
        }, 700);
      })
      .catch(() => setError('Could not open camera. Use manual barcode input fallback.'));

    return () => {
      if (timer) window.clearInterval(timer);
      if (stream) stream.getTracks().forEach((track) => track.stop());
    };
  }, [scannerRunning]);

  async function saveBatchLocation() {
    if (!scanLocation.trim() || scanQueue.length === 0) {
      setError('Enter location and scan at least one barcode.');
      return;
    }

    const count = await batchUpdateLocation(scanLocation.trim(), scanQueue);
    setError(`Batch complete: ${count} medicine locations updated.`);
    setScanQueue([]);
    await loadMedicines();
  }

  const groupedByLocation = useMemo(() => {
    const map = new Map<string, number>();
    items.forEach((item) => map.set(item.location_code, (map.get(item.location_code) ?? 0) + 1));
    return [...map.entries()].sort((a, b) => a[0].localeCompare(b[0]));
  }, [items]);

  if (loading) return <main className="container"><p>Loading...</p></main>;

  return (
    <main className="container">
      <h1>Pharmacy Medicine Locator</h1>
      <p className="muted">Built for local hosting (IIS/XAMPP + MySQL). Focus: quickly finding medicine locations in-store.</p>

      {!user ? (
        <section className="panel">
          <h2>Login</h2>
          <form className="grid" onSubmit={handleLogin}>
            <label>Username<input value={username} onChange={(e) => setUsername(e.target.value)} /></label>
            <label>Password<input type="password" value={password} onChange={(e) => setPassword(e.target.value)} /></label>
            <button type="submit">Sign in</button>
          </form>
          {error && <p className="error">{error}</p>}
        </section>
      ) : (
        <>
          <section className="panel">
            <div className="row between"><h2>Search medicines</h2><button className="secondary" onClick={handleLogout}>Logout</button></div>
            <div className="search-row">
              <input placeholder="Search brand/generic/barcode" value={q} onChange={(e) => setQ(e.target.value)} />
              <input placeholder="Filter location code" value={locationFilter} onChange={(e) => setLocationFilter(e.target.value)} />
              <select value={typeFilter} onChange={(e) => setTypeFilter(e.target.value as MedicineType | 'all')}>
                {types.map((t) => <option key={t} value={t}>{t}</option>)}
              </select>
              <label className="checkbox-row"><input type="checkbox" checked={prescriptionOnly} onChange={(e) => setPrescriptionOnly(e.target.checked)} />Prescription only</label>
            </div>
          </section>

          <section className="panel">
            <h2>{editingId ? 'Edit medicine' : 'Add medicine'}</h2>
            <form className="grid" onSubmit={handleSaveMedicine}>
              <label>Brand*<input required value={editing.brand_name} onChange={(e) => setEditing({ ...editing, brand_name: e.target.value })} /></label>
              <label>Generic*<input required value={editing.generic_name} onChange={(e) => setEditing({ ...editing, generic_name: e.target.value })} /></label>
              <label>Uses<textarea value={editing.uses_text} onChange={(e) => setEditing({ ...editing, uses_text: e.target.value })} /></label>
              <label>Dosage<textarea value={editing.dosage} onChange={(e) => setEditing({ ...editing, dosage: e.target.value })} /></label>
              <label>Type<select value={editing.type} onChange={(e) => setEditing({ ...editing, type: e.target.value as MedicineType })}>{types.filter((t) => t !== 'all').map((t) => <option key={t} value={t}>{t}</option>)}</select></label>
              <label>Location code*<input required value={editing.location_code} onChange={(e) => setEditing({ ...editing, location_code: e.target.value })} /></label>
              <label>Barcode<input value={editing.barcode} onChange={(e) => setEditing({ ...editing, barcode: e.target.value })} /></label>
              <label>Notes<textarea value={editing.notes} onChange={(e) => setEditing({ ...editing, notes: e.target.value })} /></label>
              <label className="checkbox-row"><input type="checkbox" checked={Boolean(editing.requires_prescription)} onChange={(e) => setEditing({ ...editing, requires_prescription: e.target.checked ? 1 : 0 })} />Requires prescription warning</label>
              <label>Package photo<input type="file" accept="image/*" onChange={async (e) => { const file = e.target.files?.[0]; if (!file) return; const url = await uploadImage(file); setEditing((v) => ({ ...v, package_photo_url: url })); }} /></label>
              <label>Location photo<input type="file" accept="image/*" onChange={async (e) => { const file = e.target.files?.[0]; if (!file) return; const url = await uploadImage(file); setEditing((v) => ({ ...v, location_photo_url: url })); }} /></label>
              <button type="submit">Save</button>
            </form>
          </section>

          <section className="panel">
            <h2>Smart batch scanner (location-first)</h2>
            <p className="muted">Enter location code once, start scanner, then scan barcodes one-by-one. All scanned items get assigned to that location.</p>
            <div className="search-row">
              <input placeholder="Location code (e.g. A-03-2)" value={scanLocation} onChange={(e) => setScanLocation(e.target.value)} />
              <button onClick={() => setScannerRunning((v) => !v)}>{scannerRunning ? 'Stop scanning' : 'Start scanning'}</button>
            </div>
            <video id="batch-video" className="video" muted playsInline />
            <ManualBarcodeInput onSubmit={queueFromManualBarcode} />
            <p>Queued barcodes: {scanQueue.length}</p>
            <div className="chips">{scanQueue.map((code) => <span key={code}>{code}</span>)}</div>
            <button onClick={saveBatchLocation}>Save scanned location updates</button>
          </section>

          <section className="panel">
            <h2>Medicine list</h2>
            {items.map((item) => (
              <article key={item.id} className="card">
                <div>
                  <h3>{item.brand_name}</h3>
                  <p className="muted">Generic: {item.generic_name}</p>
                  {item.requires_prescription === 1 && <p className="warning">⚠ Prescription required</p>}
                  <p><strong>Location:</strong> {item.location_code}</p>
                  <p><strong>Barcode:</strong> {item.barcode || 'N/A'}</p>
                  <p>{item.uses_text}</p>
                  <div className="actions-inline">
                    <button className="secondary" onClick={() => beginEdit(item)}>Edit</button>
                    <button className="secondary" onClick={() => void showAlternatives(item)}>Alternatives</button>
                    <button className="danger" onClick={() => void remove(item.id)}>Delete</button>
                  </div>
                </div>
                <div className="image-col">
                  {item.package_photo_url && <img src={item.package_photo_url} alt="Package" />}
                  {item.location_photo_url && <img src={item.location_photo_url} alt="Location" />}
                </div>
              </article>
            ))}
          </section>

          <section className="panel">
            <h2>Alternatives {selectedAlternativeOf ? `for ${selectedAlternativeOf}` : ''}</h2>
            {!alternatives.length && <p className="muted">Select a medicine then press Alternatives to show same-generic options and exact location/photos.</p>}
            <div className="list">
              {alternatives.map((alt) => (
                <article className="card" key={alt.id}>
                  <div>
                    <h3>{alt.brand_name}</h3>
                    <p><strong>Generic:</strong> {alt.generic_name}</p>
                    <p><strong>Location:</strong> {alt.location_code}</p>
                  </div>
                  <div className="image-col">
                    {alt.package_photo_url && <img src={alt.package_photo_url} alt="Alternative package" />}
                    {alt.location_photo_url && <img src={alt.location_photo_url} alt="Alternative location" />}
                  </div>
                </article>
              ))}
            </div>
          </section>

          <section className="panel">
            <h2>Location summary</h2>
            <div className="chips">{groupedByLocation.map(([code, count]) => <span key={code}>{code}: {count}</span>)}</div>
          </section>
        </>
      )}

      {error && <p className="error">{error}</p>}
    </main>
  );
}

function ManualBarcodeInput({ onSubmit }: { onSubmit: (barcode: string) => Promise<void> }) {
  const [value, setValue] = useState('');

  return (
    <form
      className="search-row"
      onSubmit={(e) => {
        e.preventDefault();
        void onSubmit(value);
        setValue('');
      }}
    >
      <input placeholder="Manual barcode fallback" value={value} onChange={(e) => setValue(e.target.value)} />
      <button type="submit" className="secondary">Add barcode</button>
    </form>
  );
}
