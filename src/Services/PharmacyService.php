<?php

class PharmacyService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function currentPharmacyId(): ?int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['pharmacy_id']) ? (int) $_SESSION['pharmacy_id'] : null;
    }

    public function setCurrentPharmacy(int $pharmacyId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['pharmacy_id'] = $pharmacyId;
    }

    public function activePharmacies(): array
    {
        $stmt = $this->pdo->query('SELECT id, name, status FROM pharmacies WHERE status = "active" ORDER BY name');
        return $stmt->fetchAll();
    }

    public function getActivePharmacy(int $pharmacyId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name FROM pharmacies WHERE id = ? AND status = "active"');
        $stmt->execute([$pharmacyId]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $status): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO pharmacies (name, status) VALUES (?, ?)');
        $stmt->execute([$name, $status]);
        return (int) $this->pdo->lastInsertId();
    }
}
