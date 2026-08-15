<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();

$statusFilter = $_GET['status'] ?? '';
$projectFilter = $_GET['project_id'] ?? '';
$search = trim($_GET['q'] ?? '');

$taskRepo = new TaskRepository();
$projectRepo = new ProjectRepository();

$tasks = $taskRepo->search($statusFilter, $projectFilter, $search);
$projects = $projectRepo->all();

$pageTitle = 'Tasks';
$activeNav = 'tasks';
require __DIR__ . '/../../includes/header.php';
?>

<div class="flex-between mb-16">
    <form method="get" class="flex gap-8">
        <input type="text" name="q" placeholder="Search tasks…" value="<?= e($search) ?>" style="width:200px;">
        <select name="project_id" onchange="this.form.submit()">
            <option value="">All projects</option>
            <?php foreach ($projects as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $projectFilter == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <?php foreach (['todo','in_progress','review','done','blocked'] as $s): ?>
                <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Filter</button>
    </form>
    <?php if (userCan('tasks.create')): ?>
    <a href="<?= url('modules/tasks/edit.php') ?>" class="btn btn-primary">+ New Task</a>
    <?php endif; ?>
</div>

<div class="card">
    <?php if (!$tasks): ?>
        <div class="empty-state"><div class="glyph">∅</div>No tasks match your filters.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>Task</th><th>Project</th><th>Assignee</th><th>Priority</th><th>Status</th><th>Progress</th><th>Due</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($tasks as $t): ?>
            <tr onclick="location.href='<?= url('modules/tasks/view.php?id=' . $t['id']) ?>'" style="cursor:pointer;">
                <td style="font-weight:500;"><?= e($t['title']) ?></td>
                <td class="muted"><?= e($t['project_name']) ?></td>
                <td><?= e($t['assignee_name'] ?? '—') ?></td>
                <td><span class="<?= priorityBadgeClass($t['priority']) ?>"><?= e($t['priority']) ?></span></td>
                <td><span class="<?= statusBadgeClass($t['status']) ?>"><?= e(str_replace('_',' ',$t['status'])) ?></span></td>
                <td style="width:100px;"><div class="progress-track"><div class="progress-fill" style="width:<?= (int)$t['progress'] ?>%"></div></div></td>
                <td class="mono muted"><?= formatDate($t['due_date']) ?></td>
                <td onclick="event.stopPropagation()">
                    <?php if (userCan('tasks.delete')): ?>
                    <form method="post" action="<?= url('modules/tasks/delete.php') ?>"
                          onsubmit="return confirm('Delete task &quot;<?= e($t['title']) ?>&quot;?');">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
