import { useMemo, useState } from 'react';
import { uploadMedicineImage } from '../services/medicineService';
import type { Medicine, MedicineInput, MedicineType } from '../types/medicine';

interface MedicineFormProps {
  initialValue?: Medicine;
  onSubmit: (input: MedicineInput) => Promise<void>;
  onCancel?: () => void;
  submitting: boolean;
}

const medicineTypes: MedicineType[] = ['tablet', 'capsule', 'syrup', 'injection', 'cream', 'drops', 'other'];

const emptyForm: MedicineInput = {
  brandName: '',
  genericName: '',
  uses: '',
  dosage: '',
  type: 'tablet',
  shelfCode: '',
  zone: '',
  notes: '',
  aliases: [],
  locationPhotoUrl: '',
  packagePhotoUrl: '',
  ocrTextRaw: ''
};

export function MedicineForm({ initialValue, onSubmit, onCancel, submitting }: MedicineFormProps) {
  const [form, setForm] = useState<MedicineInput>(initialValue ? { ...initialValue } : emptyForm);
  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const title = useMemo(() => (initialValue ? 'Edit medicine' : 'Add medicine'), [initialValue]);

  async function handleImageUpload(file: File, field: 'packagePhotoUrl' | 'locationPhotoUrl') {
    setUploading(true);
    setError(null);
    try {
      const url = await uploadMedicineImage(file, field === 'packagePhotoUrl' ? 'package' : 'location');
      setForm((current) => ({ ...current, [field]: url }));
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Image upload failed');
    } finally {
      setUploading(false);
    }
  }

  function updateField<K extends keyof MedicineInput>(key: K, value: MedicineInput[K]) {
    setForm((current) => ({ ...current, [key]: value }));
  }

  return (
    <form
      className="panel"
      onSubmit={(event) => {
        event.preventDefault();
        void onSubmit({
          ...form,
          aliases: form.aliases.filter(Boolean)
        });
      }}
    >
      <h2>{title}</h2>
      <div className="grid">
        <label>
          Brand name*
          <input required value={form.brandName} onChange={(event) => updateField('brandName', event.target.value)} />
        </label>
        <label>
          Generic name*
          <input required value={form.genericName} onChange={(event) => updateField('genericName', event.target.value)} />
        </label>
        <label>
          Uses
          <textarea value={form.uses} onChange={(event) => updateField('uses', event.target.value)} rows={2} />
        </label>
        <label>
          Dosage
          <textarea value={form.dosage} onChange={(event) => updateField('dosage', event.target.value)} rows={2} />
        </label>
        <label>
          Type
          <select value={form.type} onChange={(event) => updateField('type', event.target.value as MedicineType)}>
            {medicineTypes.map((type) => (
              <option key={type} value={type}>
                {type}
              </option>
            ))}
          </select>
        </label>
        <label>
          Zone
          <input value={form.zone} onChange={(event) => updateField('zone', event.target.value)} placeholder="A" />
        </label>
        <label>
          Shelf code*
          <input
            required
            value={form.shelfCode}
            onChange={(event) => updateField('shelfCode', event.target.value)}
            placeholder="A-03-2"
          />
        </label>
        <label>
          Aliases (comma-separated)
          <input
            value={form.aliases.join(', ')}
            onChange={(event) => updateField('aliases', event.target.value.split(',').map((name) => name.trim()))}
          />
        </label>
        <label>
          Notes
          <textarea value={form.notes} onChange={(event) => updateField('notes', event.target.value)} rows={2} />
        </label>
        <label>
          OCR raw text
          <textarea value={form.ocrTextRaw} onChange={(event) => updateField('ocrTextRaw', event.target.value)} rows={2} />
        </label>
      </div>

      <div className="upload-row">
        <label>
          Package photo
          <input type="file" accept="image/*" onChange={(event) => event.target.files?.[0] && void handleImageUpload(event.target.files[0], 'packagePhotoUrl')} />
        </label>
        <label>
          Shelf/location photo
          <input type="file" accept="image/*" onChange={(event) => event.target.files?.[0] && void handleImageUpload(event.target.files[0], 'locationPhotoUrl')} />
        </label>
      </div>

      {(form.packagePhotoUrl || form.locationPhotoUrl) && (
        <div className="preview-row">
          {form.packagePhotoUrl && <img src={form.packagePhotoUrl} alt="Package" />}
          {form.locationPhotoUrl && <img src={form.locationPhotoUrl} alt="Shelf" />}
        </div>
      )}

      {error && <p className="error">{error}</p>}

      <div className="button-row">
        <button type="submit" disabled={submitting || uploading}>
          {submitting ? 'Saving...' : 'Save medicine'}
        </button>
        {onCancel && (
          <button type="button" className="secondary" onClick={onCancel}>
            Cancel
          </button>
        )}
      </div>
    </form>
  );
}
