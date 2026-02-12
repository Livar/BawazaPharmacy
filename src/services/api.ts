import type { Medicine, User } from '../types/medicine';

async function parseJson<T>(response: Response): Promise<T> {
  const data = (await response.json()) as T & { error?: string };
  if (!response.ok && 'error' in data) {
    throw new Error(data.error || 'Request failed');
  }
  return data;
}

export async function authMe(): Promise<User | null> {
  const response = await fetch('/api/auth.php?action=me', { credentials: 'include' });
  const data = await parseJson<{ user: User | null }>(response);
  return data.user;
}

export async function authLogin(username: string, password: string): Promise<User> {
  const response = await fetch('/api/auth.php', {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'login', username, password })
  });
  const data = await parseJson<{ user: User }>(response);
  return data.user;
}

export async function authLogout(): Promise<void> {
  await fetch('/api/auth.php', {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'logout' })
  });
}

export async function listMedicines(params: {
  q?: string;
  type?: string;
  location?: string;
  prescriptionOnly?: boolean;
}): Promise<Medicine[]> {
  const query = new URLSearchParams();
  if (params.q) query.set('q', params.q);
  if (params.type && params.type !== 'all') query.set('type', params.type);
  if (params.location) query.set('location', params.location);
  if (params.prescriptionOnly) query.set('prescription_only', '1');

  const response = await fetch(`/api/medicines.php?action=list&${query.toString()}`, { credentials: 'include' });
  const data = await parseJson<{ items: Medicine[] }>(response);
  return data.items;
}

export async function saveMedicine(payload: Partial<Medicine>): Promise<void> {
  const response = await fetch('/api/medicines.php', {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'save', ...payload })
  });
  await parseJson<{ ok: boolean }>(response);
}

export async function deleteMedicine(id: number): Promise<void> {
  const response = await fetch('/api/medicines.php', {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'delete', id })
  });
  await parseJson<{ ok: boolean }>(response);
}

export async function alternativesFor(id: number): Promise<Medicine[]> {
  const response = await fetch(`/api/medicines.php?action=alternatives&id=${id}`, { credentials: 'include' });
  const data = await parseJson<{ items: Medicine[] }>(response);
  return data.items;
}

export async function batchUpdateLocation(locationCode: string, barcodes: string[]): Promise<number> {
  const response = await fetch('/api/medicines.php', {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'batch-location-update', location_code: locationCode, barcodes })
  });
  const data = await parseJson<{ updated_count: number }>(response);
  return data.updated_count;
}

export async function uploadImage(file: File): Promise<string> {
  const formData = new FormData();
  formData.append('image', file);

  const response = await fetch('/api/upload.php', {
    method: 'POST',
    credentials: 'include',
    body: formData
  });

  const data = await parseJson<{ url: string }>(response);
  return data.url;
}
