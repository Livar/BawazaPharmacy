import { useEffect, useMemo, useState } from 'react';
import Fuse from 'fuse.js';
import { createMedicine, listMedicines, removeMedicine, updateMedicine } from '../services/medicineService';
import type { Medicine, MedicineInput, MedicineType } from '../types/medicine';

interface MedicineFilters {
  query: string;
  type: MedicineType | 'all';
  shelfCode: string;
}

const defaultFilters: MedicineFilters = {
  query: '',
  type: 'all',
  shelfCode: ''
};

export function useMedicines() {
  const [items, setItems] = useState<Medicine[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [filters, setFilters] = useState<MedicineFilters>(defaultFilters);

  useEffect(() => {
    void refresh();
  }, []);

  const fuse = useMemo(
    () =>
      new Fuse(items, {
        keys: ['brandName', 'genericName', 'uses', 'aliases', 'shelfCode', 'zone'],
        threshold: 0.32,
        includeScore: true
      }),
    [items]
  );

  const filteredItems = useMemo(() => {
    const typed = filters.type === 'all' ? items : items.filter((item) => item.type === filters.type);
    const shelved = filters.shelfCode
      ? typed.filter((item) => item.shelfCode.toLowerCase().includes(filters.shelfCode.toLowerCase()))
      : typed;

    if (!filters.query.trim()) {
      return shelved;
    }

    const resultIds = new Set(fuse.search(filters.query).map((entry) => entry.item.id));
    return shelved.filter((item) => resultIds.has(item.id));
  }, [filters.query, filters.shelfCode, filters.type, fuse, items]);

  async function refresh() {
    setLoading(true);
    setError(null);
    try {
      const all = await listMedicines();
      setItems(all);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load medicines');
    } finally {
      setLoading(false);
    }
  }

  async function create(input: MedicineInput) {
    setSaving(true);
    try {
      await createMedicine(input);
      await refresh();
    } finally {
      setSaving(false);
    }
  }

  async function update(id: string, input: MedicineInput) {
    setSaving(true);
    try {
      await updateMedicine(id, input);
      await refresh();
    } finally {
      setSaving(false);
    }
  }

  async function remove(id: string) {
    setSaving(true);
    try {
      await removeMedicine(id);
      await refresh();
    } finally {
      setSaving(false);
    }
  }

  return {
    items: filteredItems,
    loading,
    saving,
    error,
    filters,
    setFilters,
    refresh,
    create,
    update,
    remove
  };
}
