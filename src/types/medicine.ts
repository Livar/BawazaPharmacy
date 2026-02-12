export type MedicineType = 'tablet' | 'capsule' | 'syrup' | 'injection' | 'cream' | 'drops' | 'other';

export interface Medicine {
  id: number;
  brand_name: string;
  generic_name: string;
  uses_text: string;
  dosage: string;
  type: MedicineType;
  location_code: string;
  location_photo_url: string;
  package_photo_url: string;
  barcode: string;
  notes: string;
  requires_prescription: number;
}

export interface User {
  id: number;
  username: string;
}
