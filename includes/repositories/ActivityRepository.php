<?php
require_once __DIR__ . '/BaseRepository.php';

class ActivityRepository extends BaseRepository
{
    public function log(?int $userId, string $entityType, ?int $entityId, string $action, string $description): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO activity_log (user_id, entity_type, entity_id, action, description)
             VALUES (:user_id, :entity_type, :entity_id, :action, :description)'
        );
        $stmt->execute([
            'user_id'     => $userId,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'action'      => $action,
            'description' => $description,
        ]);
    }

    public function recent(int $limit = 8): array
    {
        $sql = "SELECT a.*, u.full_name FROM activity_log a
                LEFT JOIN users u ON u.id = a.user_id
                ORDER BY a.created_at DESC LIMIT " . (int)$limit;
        return $this->db->query($sql)->fetchAll();
    }
}
