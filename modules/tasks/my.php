<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();
$user = currentUser();

$statusFilter = $_GET['status'] ?? '';
$taskRepo = new TaskRepository();
$tasks = $taskRepo->forUser((int)$user['id'], $statusFilter);

$overdue = array_filter($tasks, fn($t) => $t['due_date'] && $t['due_date'] < date('Y-m-d') && $t['status'] !== 'done');

$pageTitle = 'My Tasks';
$activeNav = 'my-tasks';
require __DIR__ . '/../../includes/header.php';
?>

<div class="flex-between mb-16">
    <form method="get" class="flex gap-8">
        <select name="status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <?php foreach (['todo','in_progress','review','done','blocked'] as $s): ?>
                <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php if ($overdue): ?>
        <span class="badge badge-priority-critical"><?= count($overdue) ?> overdue</span>
    <?php endif; ?>
</div>

<div class="card">
    <?php if (!$tasks): ?>
        <div class="empty-state"><div class="glyph">∅</div>No tasks assigned to you.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>Task</th><th>Project</th><th>Priority</th><th>Status</th><th>Progress</th><th>Due</th></tr></thead>
        <tbody>
        <?php foreach ($tasks as $t):
            $isOverdue = $t['due_date'] && $t['due_date'] < date('Y-m-d') && $t['status'] !== 'done';
        ?>
            <tr onclick="location.href='<?= url('modules/tasks/view.php?id=' . $t['id']) ?>'" style="cursor:pointer;">
                <td style="font-weight:500;"><?= e($t['title']) ?></td>
                <td class="muted"><?= e($t['project_name']) ?></td>
                <td><span class="<?= priorityBadgeClass($t['priority']) ?>"><?= e($t['priority']) ?></span></td>
                <td><span class="<?= statusBadgeClass($t['status']) ?>"><?= e(str_replace('_',' ',$t['status'])) ?></span></td>
                <td style="width:100px;"><div class="progress-track"><div class="progress-fill" style="width:<?= (int)$t['progress'] ?>%"></div></div></td>
                <td class="mono" style="color:<?= $isOverdue ? 'var(--danger)' : 'var(--text-faint)' ?>"><?= formatDate($t['due_date']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
