import type { Medicine } from '../types/medicine';

interface ShelfMapProps {
  items: Medicine[];
}

export function ShelfMap({ items }: ShelfMapProps) {
  const grouped = Object.entries(
    items.reduce<Record<string, Medicine[]>>((acc, medicine) => {
      const key = `${medicine.zone || '-'} / ${medicine.shelfCode}`;
      acc[key] = acc[key] ? [...acc[key], medicine] : [medicine];
      return acc;
    }, {})
  ).sort(([a], [b]) => a.localeCompare(b));

  return (
    <section className="panel">
      <h2>Shelf map</h2>
      {!grouped.length && <p className="muted">No shelf data yet.</p>}
      <div className="list">
        {grouped.map(([key, medicines]) => (
          <article key={key} className="card">
            <div>
              <h3>{key}</h3>
              <p className="muted">{medicines.length} medicine(s)</p>
            </div>
            <div>
              {medicines.slice(0, 5).map((medicine) => (
                <p key={medicine.id}>{medicine.brandName}</p>
              ))}
              {medicines.length > 5 && <p>+{medicines.length - 5} more</p>}
            </div>
          </article>
        ))}
      </div>
    </section>
  );
}
