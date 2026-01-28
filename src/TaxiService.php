<?php

class TaxiService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getOrCreateDriver(int $pharmacyId, string $name, string $phone): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM taxi_drivers WHERE pharmacy_id = ? AND name = ? AND phone = ?');
        $stmt->execute([$pharmacyId, $name, $phone]);
        $row = $stmt->fetch();
        if ($row) {
            return (int) $row['id'];
        }

        $insert = $this->pdo->prepare('INSERT INTO taxi_drivers (pharmacy_id, name, phone, status, balance_iqd, balance_usd) VALUES (?, ?, ?, "active", 0, 0)');
        $insert->execute([$pharmacyId, $name, $phone]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateBalance(int $driverId, float $amount, string $currency, string $direction): void
    {
        $column = $currency === 'USD' ? 'balance_usd' : 'balance_iqd';
        $operator = $direction === 'add' ? '+' : '-';
        $stmt = $this->pdo->prepare("UPDATE taxi_drivers SET {$column} = {$column} {$operator} ? WHERE id = ?");
        $stmt->execute([$amount, $driverId]);
    }
}
