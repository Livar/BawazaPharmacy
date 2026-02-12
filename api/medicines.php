<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$userId = require_auth();
$method = $_SERVER['REQUEST_METHOD'];
$data = read_json_input();
$action = $data['action'] ?? ($_GET['action'] ?? 'list');

if ($action === 'list' && $method === 'GET') {
    $search = trim((string) ($_GET['q'] ?? ''));
    $type = trim((string) ($_GET['type'] ?? ''));
    $location = trim((string) ($_GET['location'] ?? ''));
    $prescriptionOnly = ($_GET['prescription_only'] ?? '') === '1';

    $sql = 'SELECT * FROM medicines WHERE user_id = :user_id';
    $params = ['user_id' => $userId];

    if ($search !== '') {
        $sql .= ' AND (brand_name LIKE :search OR generic_name LIKE :search OR uses_text LIKE :search OR barcode = :barcode_exact)';
        $params['search'] = '%' . $search . '%';
        $params['barcode_exact'] = $search;
    }

    if ($type !== '' && $type !== 'all') {
        $sql .= ' AND type = :type';
        $params['type'] = $type;
    }

    if ($location !== '') {
        $sql .= ' AND location_code LIKE :location';
        $params['location'] = '%' . $location . '%';
    }

    if ($prescriptionOnly) {
        $sql .= ' AND requires_prescription = 1';
    }

    $sql .= ' ORDER BY brand_name ASC LIMIT 1000';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    respond(['items' => $stmt->fetchAll()]);
}

if ($action === 'save' && $method === 'POST') {
    $id = isset($data['id']) ? (int) $data['id'] : 0;
    $payload = [
        'brand_name' => trim((string) ($data['brand_name'] ?? '')),
        'generic_name' => trim((string) ($data['generic_name'] ?? '')),
        'uses_text' => trim((string) ($data['uses_text'] ?? '')),
        'dosage' => trim((string) ($data['dosage'] ?? '')),
        'type' => trim((string) ($data['type'] ?? 'other')),
        'location_code' => trim((string) ($data['location_code'] ?? '')),
        'location_photo_url' => trim((string) ($data['location_photo_url'] ?? '')),
        'package_photo_url' => trim((string) ($data['package_photo_url'] ?? '')),
        'barcode' => trim((string) ($data['barcode'] ?? '')),
        'notes' => trim((string) ($data['notes'] ?? '')),
        'requires_prescription' => !empty($data['requires_prescription']) ? 1 : 0,
    ];

    if ($payload['brand_name'] === '' || $payload['generic_name'] === '' || $payload['location_code'] === '') {
        respond(['error' => 'Brand name, generic name, and location code are required.'], 422);
    }

    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE medicines SET brand_name=:brand_name, generic_name=:generic_name, uses_text=:uses_text, dosage=:dosage, type=:type, location_code=:location_code, location_photo_url=:location_photo_url, package_photo_url=:package_photo_url, barcode=:barcode, notes=:notes, requires_prescription=:requires_prescription, updated_at=NOW() WHERE id=:id AND user_id=:user_id');
        $stmt->execute($payload + ['id' => $id, 'user_id' => $userId]);
        respond(['ok' => true, 'id' => $id]);
    }

    $stmt = $pdo->prepare('INSERT INTO medicines (user_id, brand_name, generic_name, uses_text, dosage, type, location_code, location_photo_url, package_photo_url, barcode, notes, requires_prescription, created_at, updated_at) VALUES (:user_id, :brand_name, :generic_name, :uses_text, :dosage, :type, :location_code, :location_photo_url, :package_photo_url, :barcode, :notes, :requires_prescription, NOW(), NOW())');
    $stmt->execute($payload + ['user_id' => $userId]);
    respond(['ok' => true, 'id' => (int) $pdo->lastInsertId()], 201);
}

if ($action === 'delete' && $method === 'POST') {
    $id = (int) ($data['id'] ?? 0);
    if ($id <= 0) {
        respond(['error' => 'Valid medicine id is required.'], 422);
    }

    $stmt = $pdo->prepare('DELETE FROM medicines WHERE id=:id AND user_id=:user_id');
    $stmt->execute(['id' => $id, 'user_id' => $userId]);
    respond(['ok' => true]);
}

if ($action === 'alternatives' && $method === 'GET') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
        respond(['error' => 'Valid medicine id is required.'], 422);
    }

    $baseStmt = $pdo->prepare('SELECT id, generic_name FROM medicines WHERE id = :id AND user_id = :user_id LIMIT 1');
    $baseStmt->execute(['id' => $id, 'user_id' => $userId]);
    $base = $baseStmt->fetch();

    if (!$base) {
        respond(['items' => []]);
    }

    $stmt = $pdo->prepare('SELECT id, brand_name, generic_name, location_code, location_photo_url, package_photo_url, requires_prescription FROM medicines WHERE user_id = :user_id AND generic_name = :generic_name AND id <> :id ORDER BY brand_name ASC');
    $stmt->execute(['user_id' => $userId, 'generic_name' => $base['generic_name'], 'id' => $id]);
    respond(['items' => $stmt->fetchAll()]);
}

if ($action === 'batch-location-update' && $method === 'POST') {
    $locationCode = trim((string) ($data['location_code'] ?? ''));
    $barcodes = $data['barcodes'] ?? [];

    if ($locationCode === '' || !is_array($barcodes) || count($barcodes) === 0) {
        respond(['error' => 'Location code and at least one barcode are required.'], 422);
    }

    $updateStmt = $pdo->prepare('UPDATE medicines SET location_code = :location_code, updated_at = NOW() WHERE user_id = :user_id AND barcode = :barcode');
    $logStmt = $pdo->prepare('INSERT INTO scan_logs (user_id, barcode, location_code, scanned_at) VALUES (:user_id, :barcode, :location_code, NOW())');

    $updated = 0;
    foreach ($barcodes as $barcodeRaw) {
      $barcode = trim((string) $barcodeRaw);
      if ($barcode === '') {
        continue;
      }
      $updateStmt->execute(['location_code' => $locationCode, 'user_id' => $userId, 'barcode' => $barcode]);
      $updated += $updateStmt->rowCount();
      $logStmt->execute(['user_id' => $userId, 'barcode' => $barcode, 'location_code' => $locationCode]);
    }

    respond(['ok' => true, 'updated_count' => $updated]);
}

respond(['error' => 'Unsupported action.'], 405);
