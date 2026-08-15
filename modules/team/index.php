<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();

$userRepo = new UserRepository();
$members = $userRepo->withWorkload();

$pageTitle = 'Team';
$activeNav = 'team';
require __DIR__ . '/../../includes/header.php';
?>

<div class="flex-between mb-16">
    <div class="muted" style="font-size:13px;"><?= count($members) ?> members</div>
    <?php if (userCan('team.manage')): ?>
    <a href="<?= url('modules/team/edit.php') ?>" class="btn btn-primary">+ New Member</a>
    <?php endif; ?>
</div>

<div class="grid grid-3">
    <?php foreach ($members as $m): ?>
        <div class="card">
            <div class="flex gap-12 mb-16">
                <div class="avatar" style="width:40px;height:40px;font-size:14px;background:<?= e($m['avatar_color']) ?>;"><?= e(initials($m['full_name'])) ?></div>
                <div>
                    <div style="font-weight:600;"><?= e($m['full_name']) ?></div>
                    <div class="muted" style="font-size:12px;"><?= e($m['job_title'] ?: ucfirst($m['role'])) ?></div>
                </div>
            </div>
            <div class="grid grid-3" style="gap:8px; text-align:center;">
                <div>
                    <div class="stat-value" style="font-size:18px;"><?= (int)$m['open_tasks'] ?></div>
                    <div class="muted" style="font-size:10.5px;">Open</div>
                </div>
                <div>
                    <div class="stat-value" style="font-size:18px;"><?= (int)$m['done_tasks'] ?></div>
                    <div class="muted" style="font-size:10.5px;">Done</div>
                </div>
                <div>
                    <div class="stat-value" style="font-size:18px; text-transform:capitalize;"><?= e($m['status']) ?></div>
                    <div class="muted" style="font-size:10.5px;">Status</div>
                </div>
            </div>
            <?php if (userCan('team.manage')): ?>
            <div class="flex gap-8" style="margin-top:14px;">
                <a href="<?= url('modules/team/edit.php?id=' . $m['id']) ?>" class="btn btn-sm btn-block">Edit</a>
                <?php if ($m['id'] != currentUser()['id']): ?>
                <form method="post" action="<?= url('modules/team/delete.php') ?>" style="flex:1;"
                      onsubmit="return confirm('Remove &quot;<?= e($m['full_name']) ?>&quot; from the team?');">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger btn-block">Remove</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
