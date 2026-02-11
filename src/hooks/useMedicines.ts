import { useEffect, useMemo, useState } from 'react';
import Fuse from 'fuse.js';
import {
  bulkCreateMedicines,
  createMedicine,
  listMedicines,
  removeMedicine,
  updateMedicine
} from '../services/medicineService';
import type { Medicine, MedicineInput, MedicineType } from '../types/medicine';

interface MedicineFilters {
  query: string;
  type: MedicineType | 'all';
  shelfCode: string;
  prescriptionOnly: boolean;
}

const defaultFilters: MedicineFilters = {
  query: '',
  type: 'all',
  shelfCode: '',
  prescriptionOnly: false
};

export function useMedicines(enabled = true) {
  const [allItems, setAllItems] = useState<Medicine[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [filters, setFilters] = useState<MedicineFilters>(defaultFilters);

  useEffect(() => {
    if (!enabled) {
      setLoading(false);
      setAllItems([]);
      return;
    }
    void refresh();
  }, [enabled]);

  const fuse = useMemo(
    () =>
      new Fuse(allItems, {
        keys: ['brandName', 'genericName', 'uses', 'aliases', 'shelfCode', 'zone', 'barcode'],
        threshold: 0.32,
        includeScore: true
      }),
    [allItems]
  );

  const filteredItems = useMemo(() => {
    const typed = filters.type === 'all' ? allItems : allItems.filter((item) => item.type === filters.type);
    const shelved = filters.shelfCode
      ? typed.filter((item) => item.shelfCode.toLowerCase().includes(filters.shelfCode.toLowerCase()))
      : typed;
    const prescribed = filters.prescriptionOnly ? shelved.filter((item) => item.requiresPrescription) : shelved;

    if (!filters.query.trim()) {
      return prescribed;
    }

    const resultIds = new Set(fuse.search(filters.query).map((entry) => entry.item.id));
    return prescribed.filter((item) => resultIds.has(item.id));
  }, [filters.prescriptionOnly, filters.query, filters.shelfCode, filters.type, fuse, allItems]);

  async function refresh() {
    if (!enabled) {
      return;
    }

    setLoading(true);
    setError(null);
    try {
      const all = await listMedicines();
      setAllItems(all);
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

  async function createBulk(inputs: MedicineInput[]) {
    setSaving(true);
    try {
      await bulkCreateMedicines(inputs);
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
    allItems,
    items: filteredItems,
    loading,
    saving,
    error,
    filters,
    setFilters,
    refresh,
    create,
    createBulk,
    update,
    remove
  };
}
