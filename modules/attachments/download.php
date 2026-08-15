<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();
$id = (int)($_GET['id'] ?? 0);
$attachment = (new AttachmentRepository())->find($id);

if (!$attachment) {
    http_response_code(404);
    die('File not found.');
}

$path = __DIR__ . '/../../uploads/' . basename($attachment['stored_path']);

if (!is_file($path)) {
    http_response_code(404);
    die('File is missing from storage.');
}

header('Content-Type: ' . ($attachment['mime_type'] ?: 'application/octet-stream'));
header('Content-Disposition: attachment; filename="' . basename($attachment['original_name']) . '"');
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
readfile($path);
exit;
