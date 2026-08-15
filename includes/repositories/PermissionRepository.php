<?php
require_once __DIR__ . '/BaseRepository.php';

class PermissionRepository extends BaseRepository
{
    private static ?array $matrixCache = null;

    /** All permission definitions, grouped by category, for the settings screen. */
    public function catalogue(): array
    {
        return $this->db->query("SELECT * FROM permissions ORDER BY category, label")->fetchAll();
    }

    /** role => [permission_key => bool], loaded once per request. */
    private function matrix(): array
    {
        if (self::$matrixCache !== null) {
            return self::$matrixCache;
        }
        $rows = $this->db->query("SELECT role, permission_key, allowed FROM role_permissions")->fetchAll();
        $matrix = [];
        foreach ($rows as $r) {
            $matrix[$r['role']][$r['permission_key']] = (bool) $r['allowed'];
        }
        self::$matrixCache = $matrix;
        return $matrix;
    }

    public function can(string $role, string $key): bool
    {
        // Fail safe: admin can always act, even if the permissions tables
        // are missing or a key hasn't been seeded yet.
        if ($role === 'admin') {
            return true;
        }
        return $this->matrix()[$role][$key] ?? false;
    }

    public function fullMatrix(): array
    {
        return $this->matrix();
    }

    public function setPermission(string $role, string $key, bool $allowed): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO role_permissions (role, permission_key, allowed) VALUES (:r, :k, :a)
             ON DUPLICATE KEY UPDATE allowed = :a2"
        );
        $stmt->execute(['r' => $role, 'k' => $key, 'a' => (int) $allowed, 'a2' => (int) $allowed]);
        self::$matrixCache = null; // invalidate cache after a write
    }
}
