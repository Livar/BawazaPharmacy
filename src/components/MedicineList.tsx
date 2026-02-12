import type { Medicine } from '../types/medicine';

interface MedicineListProps {
  items: Medicine[];
  onEdit: (medicine: Medicine) => void;
  onDelete: (id: string) => Promise<void>;
}

export function MedicineList({ items, onEdit, onDelete }: MedicineListProps) {
  if (!items.length) {
    return <p className="muted">No medicines found. Add your first entry or change search filters.</p>;
  }

  return (
    <div className="list">
      {items.map((item) => (
        <article key={item.id} className="card">
          <div>
            <h3>{item.brandName}</h3>
            <p className="muted">Generic: {item.genericName}</p>
            {item.requiresPrescription && <p className="warning">⚠ Prescription required</p>}
            <p>{item.uses || 'No uses provided yet.'}</p>
            <p>
              <strong>Dosage:</strong> {item.dosage || 'N/A'}
            </p>
            <p>
              <strong>Type:</strong> {item.type}
            </p>
            <p>
              <strong>Location:</strong> Zone {item.zone || '-'} / Shelf {item.shelfCode}
            </p>
            {item.barcode && (
              <p>
                <strong>Barcode:</strong> {item.barcode}
              </p>
            )}
          </div>
          <div className="actions">
            {item.packagePhotoUrl && (
              <a href={item.packagePhotoUrl} target="_blank" rel="noreferrer">
                Package photo
              </a>
            )}
            {item.locationPhotoUrl && (
              <a href={item.locationPhotoUrl} target="_blank" rel="noreferrer">
                Shelf photo
              </a>
            )}
            <button onClick={() => onEdit(item)} className="secondary">
              Edit
            </button>
            <button onClick={() => void onDelete(item.id)} className="danger">
              Delete
            </button>
          </div>
        </article>
      ))}
    </div>
  );
}
