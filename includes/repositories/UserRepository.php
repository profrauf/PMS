<?php
require_once __DIR__ . '/BaseRepository.php';

class UserRepository extends BaseRepository
{
    public function activeList(): array
    {
        return $this->db->query(
            "SELECT id, full_name FROM users WHERE status = 'active' AND deleted_at IS NULL ORDER BY full_name"
        )->fetchAll();
    }

    /** Team roster with open/done task counts, for the Team page. */
    public function withWorkload(): array
    {
        return $this->db->query(
            "SELECT u.*,
                (SELECT COUNT(*) FROM tasks t WHERE t.assigned_to = u.id AND t.status != 'done' AND t.deleted_at IS NULL) AS open_tasks,
                (SELECT COUNT(*) FROM tasks t WHERE t.assigned_to = u.id AND t.status = 'done' AND t.deleted_at IS NULL) AS done_tasks
             FROM users u WHERE u.deleted_at IS NULL ORDER BY u.full_name"
        )->fetchAll();
    }

    /** Aggregated performance metrics per member, for the reports module. */
    public function performanceReport(): array
    {
        return $this->db->query(
            "SELECT u.id, u.full_name, u.department, u.job_title,
                COUNT(t.id) AS total_tasks,
                SUM(CASE WHEN t.status = 'done' THEN 1 ELSE 0 END) AS done_tasks,
                SUM(CASE WHEN t.status != 'done' AND t.due_date IS NOT NULL AND t.due_date < CURDATE() THEN 1 ELSE 0 END) AS overdue_tasks,
                AVG(t.estimated_hours) AS avg_estimated,
                AVG(t.actual_hours) AS avg_actual
             FROM users u
             LEFT JOIN tasks t ON t.assigned_to = u.id AND t.deleted_at IS NULL
             WHERE u.deleted_at IS NULL
             GROUP BY u.id
             ORDER BY u.full_name"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email AND status = "active" AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO users (full_name, email, password_hash, role, department, job_title, phone, status, avatar_color)
                VALUES (:full_name, :email, :password_hash, :role, :department, :job_title, :phone, :status, :avatar_color)";
        $this->db->prepare($sql)->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sql = "UPDATE users SET full_name=:full_name, email=:email, role=:role, department=:department,
                job_title=:job_title, phone=:phone, status=:status WHERE id=:id";
        $data['id'] = $id;
        $this->db->prepare($sql)->execute($data);
    }

    public function updatePassword(int $id, string $hash): void
    {
        $this->db->prepare("UPDATE users SET password_hash = :h WHERE id = :id")->execute(['h' => $hash, 'id' => $id]);
    }

    public function softDelete(int $id): void
    {
        $this->db->prepare('UPDATE users SET deleted_at = NOW(), status = "inactive" WHERE id = :id')->execute(['id' => $id]);
    }

    public function countActiveAdmins(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND deleted_at IS NULL")->fetchColumn();
    }

    public function countActive(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'active' AND deleted_at IS NULL")->fetchColumn();
    }
}
