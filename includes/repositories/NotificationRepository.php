<?php
require_once __DIR__ . '/BaseRepository.php';

class NotificationRepository extends BaseRepository
{
    public function create(int $userId, ?int $actorId, string $type, string $message, ?string $link = null): void
    {
        // Don't notify someone about their own action.
        if ($actorId !== null && $actorId === $userId) {
            return;
        }
        $this->db->prepare(
            "INSERT INTO notifications (user_id, actor_id, type, message, link) VALUES (:u, :a, :t, :m, :l)"
        )->execute(['u' => $userId, 'a' => $actorId, 't' => $type, 'm' => $message, 'l' => $link]);
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :u AND is_read = 0");
        $stmt->execute(['u' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function recent(int $userId, int $limit = 10): array
    {
        $sql = "SELECT * FROM notifications WHERE user_id = :u ORDER BY created_at DESC LIMIT " . (int)$limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['u' => $userId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function markRead(int $id, int $userId): void
    {
        $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :u")
            ->execute(['id' => $id, 'u' => $userId]);
    }

    public function markAllRead(int $userId): void
    {
        $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :u")->execute(['u' => $userId]);
    }
}
