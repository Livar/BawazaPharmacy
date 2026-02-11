import { useMemo, useState } from 'react';
import Tesseract from 'tesseract.js';
import type { Medicine } from '../types/medicine';

interface OcrScannerProps {
  medicines: Medicine[];
  onUseDetectedText: (text: string, matched?: Medicine) => void;
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

  async function handleFile(file: File) {
    setProcessing(true);
    setError(null);
    setDetectedText('');

    try {
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
      <h2>Scan packet text (OCR)</h2>
      <p className="muted">
        Use your camera to take a clear packet photo, then upload it here. The app scans text and suggests a matching medicine.
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
          <h3>Detected text</h3>
          <pre>{detectedText}</pre>
        </div>
      )}
    </section>
  );
}
