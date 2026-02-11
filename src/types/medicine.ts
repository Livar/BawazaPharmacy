export type MedicineType =
  | 'tablet'
  | 'capsule'
  | 'syrup'
  | 'injection'
  | 'cream'
  | 'drops'
  | 'other';

export interface Medicine {
  id: string;
  brandName: string;
  genericName: string;
  uses: string;
  dosage: string;
  type: MedicineType;
  shelfCode: string;
  zone: string;
  notes: string;
  locationPhotoUrl?: string;
  packagePhotoUrl?: string;
  aliases: string[];
  ocrTextRaw?: string;
  createdAt: string;
  updatedAt: string;
}

export type MedicineInput = Omit<Medicine, 'id' | 'createdAt' | 'updatedAt'>;
