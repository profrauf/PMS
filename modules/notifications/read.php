<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();
$user = currentUser();

$id = (int)($_GET['id'] ?? 0);
$notifRepo = new NotificationRepository();
$notification = $notifRepo->find($id);

if ($notification && (int)$notification['user_id'] === (int)$user['id']) {
    $notifRepo->markRead($id, $user['id']);
    if ($notification['link']) {
        redirect($notification['link']);
    }
}

redirect('modules/notifications/index.php');
