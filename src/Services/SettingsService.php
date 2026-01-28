<?php

class SettingsService
{
    private PDO $pdo;
    private PharmacyService $pharmacyService;

    public function __construct(PDO $pdo, PharmacyService $pharmacyService)
    {
        $this->pdo = $pdo;
        $this->pharmacyService = $pharmacyService;
    }

    public function fetch(string $key, $default = null, ?int $pharmacyId = null)
    {
        $pharmacyId = $pharmacyId ?? $this->pharmacyService->currentPharmacyId();
        if ($pharmacyId === null) {
            return $default;
        }
        $stmt = $this->pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ? AND pharmacy_id = ?');
        $stmt->execute([$key, $pharmacyId]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    }

    public function upsert(string $key, string $value, ?int $pharmacyId = null): void
    {
        $pharmacyId = $pharmacyId ?? $this->pharmacyService->currentPharmacyId();
        if ($pharmacyId === null) {
            return;
        }
        $stmt = $this->pdo->prepare('INSERT INTO settings (pharmacy_id, setting_key, setting_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        $stmt->execute([$pharmacyId, $key, $value]);
    }

    public function appName(): string
    {
        return (string) $this->fetch('app_name', APP_DEFAULT_NAME);
    }

    public function exchangeRate(): string
    {
        return (string) $this->fetch('exchange_rate', '1500');
    }
}
