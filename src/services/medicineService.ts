import {
  addDoc,
  collection,
  deleteDoc,
  doc,
  getDocs,
  orderBy,
  query,
  setDoc,
  Timestamp
} from 'firebase/firestore';
import { getDownloadURL, ref, uploadBytes } from 'firebase/storage';
import { db, storage } from './firebase';
import type { Medicine, MedicineInput } from '../types/medicine';

const medicinesCollection = collection(db, 'medicines');

const nowTimestamp = () => Timestamp.now().toDate().toISOString();

export async function listMedicines(): Promise<Medicine[]> {
  const q = query(medicinesCollection, orderBy('brandName'));
  const snap = await getDocs(q);

  return snap.docs.map((item) => ({
    id: item.id,
    ...item.data()
  })) as Medicine[];
}

export async function createMedicine(input: MedicineInput): Promise<void> {
  const timestamp = nowTimestamp();
  await addDoc(medicinesCollection, {
    ...input,
    createdAt: timestamp,
    updatedAt: timestamp
  });
}

export async function updateMedicine(id: string, input: MedicineInput): Promise<void> {
  await setDoc(
    doc(db, 'medicines', id),
    {
      ...input,
      updatedAt: nowTimestamp()
    },
    { merge: true }
  );
}

export async function removeMedicine(id: string): Promise<void> {
  await deleteDoc(doc(db, 'medicines', id));
}

export async function uploadMedicineImage(file: File, folder: 'package' | 'location'): Promise<string> {
  const safeName = `${Date.now()}-${file.name.replace(/\s+/g, '-')}`;
  const imageRef = ref(storage, `medicine-images/${folder}/${safeName}`);
  await uploadBytes(imageRef, file);
  return getDownloadURL(imageRef);
}
