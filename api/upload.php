<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Method not allowed.'], 405);
}

if (!isset($_FILES['image']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
    respond(['error' => 'No file uploaded.'], 422);
}

$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$mime = mime_content_type($_FILES['image']['tmp_name']) ?: '';
if (!isset($allowed[$mime])) {
    respond(['error' => 'Unsupported file type.'], 422);
}

$dir = __DIR__ . '/../public/uploads';
if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
    respond(['error' => 'Could not prepare upload directory.'], 500);
}

$fileName = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
$path = $dir . '/' . $fileName;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
    respond(['error' => 'Upload failed.'], 500);
}

respond(['url' => '/uploads/' . $fileName]);
