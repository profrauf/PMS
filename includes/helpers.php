<?php
/**
 * Small, dependency-free helper functions shared across the app.
 */

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function formatDate(?string $date, string $format = 'M d, Y'): string
{
    if (empty($date) || $date === '0000-00-00') {
        return '—';
    }
    return date($format, strtotime($date));
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid or expired form submission (CSRF check failed). Please go back and try again.');
    }
}

function flash(string $key, ?string $message = null)
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function logActivity(?int $userId, string $entityType, ?int $entityId, string $action, string $description): void
{
    (new ActivityRepository())->log($userId, $entityType, $entityId, $action, $description);
}

/** Badge CSS class helpers — keep status/priority styling consistent everywhere */
function statusBadgeClass(string $status): string
{
    return 'badge badge-status-' . str_replace('_', '-', $status);
}

function priorityBadgeClass(string $priority): string
{
    return 'badge badge-priority-' . $priority;
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $letters = array_map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)), array_slice($parts, 0, 2));
    return implode('', $letters) ?: '?';
}

/**
 * Save an uploaded file under /uploads with a collision-safe name.
 * Returns the stored relative filename on success, or null on failure/rejection.
 * Whitelist-based extension check — never trusts the client-provided mime type alone.
 */
function saveUploadedFile(array $file): ?string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $maxBytes = 15 * 1024 * 1024; // 15 MB
    if ($file['size'] > $maxBytes) {
        return null;
    }

    $allowedExt = ['pdf','doc','docx','xls','xlsx','ppt','pptx','png','jpg','jpeg','gif','txt','csv','zip'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return null;
    }

    $uploadDir = __DIR__ . '/../uploads/';
    $storedName = bin2hex(random_bytes(16)) . '.' . $ext;

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $storedName)) {
        return null;
    }

    return $storedName;
}

function humanFileSize(int $bytes): string
{
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}

/** Stream an array of associative rows as a downloadable CSV file and exit. */
function exportCsv(string $filename, array $headers, array $rows): void
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel opens Arabic text correctly
    fputcsv($out, $headers);
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}
