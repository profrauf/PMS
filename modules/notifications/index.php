<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();
$user = currentUser();
$notifRepo = new NotificationRepository();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all'])) {
    verifyCsrf();
    $notifRepo->markAllRead($user['id']);
    redirect('modules/notifications/index.php');
}

$notifications = $notifRepo->recent($user['id'], 50);

$pageTitle = 'Notifications';
$activeNav = 'notifications';
require __DIR__ . '/../../includes/header.php';
?>

<div class="flex-between mb-16">
    <div class="muted" style="font-size:13px;"><?= count($notifications) ?> total</div>
    <form method="post">
        <?= csrfField() ?>
        <button type="submit" name="mark_all" value="1" class="btn btn-sm">Mark all as read</button>
    </form>
</div>

<div class="card">
    <?php if (!$notifications): ?>
        <div class="empty-state"><div class="glyph">∅</div>No notifications yet.</div>
    <?php else: ?>
    <ul style="display:flex; flex-direction:column;">
        <?php foreach ($notifications as $n): ?>
            <li style="border-bottom:1px solid var(--grid-line); padding:12px 0;">
                <a href="<?= url('modules/notifications/read.php?id=' . $n['id']) ?>" class="flex-between" style="align-items:flex-start;">
                    <div class="flex gap-8" style="align-items:flex-start;">
                        <?php if (!$n['is_read']): ?>
                            <span style="width:7px;height:7px;border-radius:50%;background:var(--accent);display:inline-block;margin-top:6px;flex-shrink:0;"></span>
                        <?php else: ?>
                            <span style="width:7px;"></span>
                        <?php endif; ?>
                        <span style="font-size:13.5px; color:<?= $n['is_read'] ? 'var(--text-dim)' : 'var(--text)' ?>;"><?= e($n['message']) ?></span>
                    </div>
                    <span class="muted mono" style="font-size:10.5px; white-space:nowrap; margin-left:12px;"><?= date('M d, H:i', strtotime($n['created_at'])) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
