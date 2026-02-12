import { useMemo, useState } from 'react';
import Tesseract from 'tesseract.js';
import type { Medicine } from '../types/medicine';

interface OcrScannerProps {
  medicines: Medicine[];
  onUseDetectedText: (text: string, matched?: Medicine) => void;
}

type BarcodeDetectorLike = {
  detect: (source: ImageBitmapSource) => Promise<Array<{ rawValue?: string }>>;
};

declare global {
  interface Window {
    BarcodeDetector?: new (options?: { formats?: string[] }) => BarcodeDetectorLike;
  }
}

function normalize(value: string) {
  return value.toLowerCase().replace(/[^a-z0-9\s]/gi, ' ');
}

export function OcrScanner({ medicines, onUseDetectedText }: OcrScannerProps) {
  const [processing, setProcessing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [detectedText, setDetectedText] = useState('');

  const lookup = useMemo(
    () =>
      medicines.map((medicine) => ({
        medicine,
        searchTokens: [medicine.brandName, medicine.genericName, ...medicine.aliases].map(normalize)
      })),
    [medicines]
  );

  async function tryBarcodeFirst(file: File): Promise<string | null> {
    if (!window.BarcodeDetector) {
      return null;
    }

    try {
      const detector = new window.BarcodeDetector();
      const bitmap = await createImageBitmap(file);
      const results = await detector.detect(bitmap);
      const code = results[0]?.rawValue?.trim();
      bitmap.close();
      return code || null;
    } catch {
      return null;
    }
  }

  async function handleFile(file: File) {
    setProcessing(true);
    setError(null);
    setDetectedText('');

    try {
      const barcode = await tryBarcodeFirst(file);
      if (barcode) {
        const matched = medicines.find((medicine) => medicine.barcode?.trim() === barcode);
        const output = `BARCODE: ${barcode}`;
        setDetectedText(output);
        onUseDetectedText(output, matched);
        return;
      }

      const result = await Tesseract.recognize(file, 'eng');
      const text = result.data.text.trim();
      setDetectedText(text);

      const normalizedText = normalize(text);
      const found = lookup.find((entry) =>
        entry.searchTokens.some((token) => token.length > 2 && normalizedText.includes(token))
      );

      onUseDetectedText(text, found?.medicine);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'OCR scan failed');
    } finally {
      setProcessing(false);
    }
  }

  return (
    <section className="panel">
      <h2>Scan packet (Barcode first, OCR fallback)</h2>
      <p className="muted">
        Use your camera to take a clear packet photo, then upload it here. The app attempts barcode detection first and
        falls back to OCR text matching.
      </p>
      <label>
        Packet image
        <input
          type="file"
          accept="image/*"
          capture="environment"
          onChange={(event) => event.target.files?.[0] && void handleFile(event.target.files[0])}
          disabled={processing}
        />
      </label>
      {processing && <p>Scanning image... this can take a few seconds.</p>}
      {error && <p className="error">{error}</p>}
      {detectedText && (
        <div className="ocr-result">
          <h3>Detected value</h3>
          <pre>{detectedText}</pre>
        </div>
      )}
    </section>
  );
}
