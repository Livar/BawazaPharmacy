<?php
require_once __DIR__ . '/db.php';

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function current_user(): ?array
{
    start_secure_session();
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    $user = current_user();
    if (!$user) {
        header('Location: index.php');
        exit;
    }
    if (!current_pharmacy_id()) {
        header('Location: index.php');
        exit;
    }
}

function require_admin(): void
{
    $user = current_user();
    if (!$user || $user['role'] !== 'admin') {
        header('Location: clock.php');
        exit;
    }
}

function regenerate_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

function csrf_token(): string
{
    start_secure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool
{
    start_secure_session();
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function current_pharmacy_id(): ?int
{
    start_secure_session();
    return isset($_SESSION['pharmacy_id']) ? (int) $_SESSION['pharmacy_id'] : null;
}

function get_pharmacies(): array
{
    $pdo = get_db_connection();
    $stmt = $pdo->query('SELECT id, name, status FROM pharmacies WHERE status = "active" ORDER BY name');
    return $stmt->fetchAll();
}

function fetch_setting(string $key, $default = null, ?int $pharmacyId = null)
{
    $pdo = get_db_connection();
    $pharmacyId = $pharmacyId ?? current_pharmacy_id();
    if ($pharmacyId === null) {
        return $default;
    }
    $stmt = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ? AND pharmacy_id = ?');
    $stmt->execute([$key, $pharmacyId]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

function upsert_setting(string $key, string $value, ?int $pharmacyId = null): void
{
    $pdo = get_db_connection();
    $pharmacyId = $pharmacyId ?? current_pharmacy_id();
    if ($pharmacyId === null) {
        return;
    }
    $stmt = $pdo->prepare('INSERT INTO settings (pharmacy_id, setting_key, setting_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    $stmt->execute([$pharmacyId, $key, $value]);
}

function app_name(): string
{
    return (string) fetch_setting('app_name', APP_DEFAULT_NAME);
}
