import {
  addDoc,
  collection,
  deleteDoc,
  doc,
  getDocs,
  orderBy,
  query,
  setDoc,
  Timestamp,
  where
} from 'firebase/firestore';
import { getDownloadURL, ref, uploadBytes } from 'firebase/storage';
import { auth, db, storage } from './firebase';
import type { Medicine, MedicineInput } from '../types/medicine';

const medicinesCollection = collection(db, 'medicines');

const nowTimestamp = () => Timestamp.now().toDate().toISOString();

function getCurrentUid() {
  const uid = auth.currentUser?.uid;
  if (!uid) {
    throw new Error('You must be logged in to manage medicines.');
  }
  return uid;
}

export async function listMedicines(): Promise<Medicine[]> {
  const uid = getCurrentUid();
  const q = query(medicinesCollection, where('ownerId', '==', uid), orderBy('brandName'));
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
    ownerId: getCurrentUid(),
    createdAt: timestamp,
    updatedAt: timestamp
  });
}

export async function bulkCreateMedicines(inputs: MedicineInput[]): Promise<void> {
  for (const item of inputs) {
    await createMedicine(item);
  }
}

export async function updateMedicine(id: string, input: MedicineInput): Promise<void> {
  await setDoc(
    doc(db, 'medicines', id),
    {
      ...input,
      ownerId: getCurrentUid(),
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
