<?php

class Notifications
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(int $pharmacyId, ?int $userId, string $message, ?string $link = null): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO notifications (pharmacy_id, user_id, message, link, is_read) VALUES (?, ?, ?, ?, 0)');
        $stmt->execute([$pharmacyId, $userId, $message, $link]);
    }

    public function latest(int $pharmacyId, int $limit = 5): array
    {
        $stmt = $this->pdo->prepare('SELECT id, message, link, is_read, created_at FROM notifications WHERE pharmacy_id = ? ORDER BY created_at DESC LIMIT ?');
        $stmt->bindValue(1, $pharmacyId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function unreadCount(int $pharmacyId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS total FROM notifications WHERE pharmacy_id = ? AND is_read = 0');
        $stmt->execute([$pharmacyId]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function markRead(int $notificationId, int $pharmacyId): void
    {
        $stmt = $this->pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND pharmacy_id = ?');
        $stmt->execute([$notificationId, $pharmacyId]);
    }
}
