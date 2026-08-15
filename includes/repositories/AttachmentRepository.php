<?php
require_once __DIR__ . '/BaseRepository.php';

class AttachmentRepository extends BaseRepository
{
    public function forTask(int $taskId): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, u.full_name FROM attachments a LEFT JOIN users u ON u.id = a.uploaded_by
             WHERE a.task_id = :id ORDER BY a.uploaded_at DESC"
        );
        $stmt->execute(['id' => $taskId]);
        return $stmt->fetchAll();
    }

    public function forProject(int $projectId): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, u.full_name FROM attachments a LEFT JOIN users u ON u.id = a.uploaded_by
             WHERE a.project_id = :id ORDER BY a.uploaded_at DESC"
        );
        $stmt->execute(['id' => $projectId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM attachments WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createForTask(int $taskId, int $userId, string $originalName, string $storedPath, int $size, ?string $mime): void
    {
        $this->db->prepare(
            "INSERT INTO attachments (task_id, uploaded_by, original_name, stored_path, file_size, mime_type)
             VALUES (:t, :u, :n, :s, :sz, :m)"
        )->execute(['t' => $taskId, 'u' => $userId, 'n' => $originalName, 's' => $storedPath, 'sz' => $size, 'm' => $mime]);
    }

    public function createForProject(int $projectId, int $userId, string $originalName, string $storedPath, int $size, ?string $mime): void
    {
        $this->db->prepare(
            "INSERT INTO attachments (project_id, uploaded_by, original_name, stored_path, file_size, mime_type)
             VALUES (:p, :u, :n, :s, :sz, :m)"
        )->execute(['p' => $projectId, 'u' => $userId, 'n' => $originalName, 's' => $storedPath, 'sz' => $size, 'm' => $mime]);
    }
}
