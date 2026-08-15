<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();

$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

$projectRepo = new ProjectRepository();
$projects = $projectRepo->search($statusFilter, $search);

$pageTitle = 'Projects';
$activeNav = 'projects';
require __DIR__ . '/../../includes/header.php';
?>

<div class="flex-between mb-16">
    <form method="get" class="flex gap-8">
        <input type="text" name="q" placeholder="Search by name or code…" value="<?= e($search) ?>" style="width:240px;">
        <select name="status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <?php foreach (['planning','active','on_hold','completed','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Filter</button>
    </form>
    <?php if (userCan('projects.create')): ?>
    <a href="<?= url('modules/projects/edit.php') ?>" class="btn btn-primary">+ New Project</a>
    <?php endif; ?>
</div>

<div class="card">
    <?php if (!$projects): ?>
        <div class="empty-state"><div class="glyph">∅</div>No projects match. Create one to get started.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>Code</th><th>Name</th><th>Owner</th><th>Tasks</th><th>Progress</th><th>Priority</th><th>Status</th><th>Due</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($projects as $p): ?>
            <tr>
                <td class="mono muted"><?= e($p['code']) ?></td>
                <td><a href="<?= url('modules/projects/view.php?id=' . $p['id']) ?>" style="font-weight:600;"><?= e($p['name']) ?></a></td>
                <td><?= e($p['owner_name'] ?? '—') ?></td>
                <td><?= (int)$p['task_count'] ?></td>
                <td style="width:110px;">
                    <div class="progress-track"><div class="progress-fill" style="width:<?= (int)$p['progress'] ?>%"></div></div>
                </td>
                <td><span class="<?= priorityBadgeClass($p['priority']) ?>"><?= e($p['priority']) ?></span></td>
                <td><span class="<?= statusBadgeClass($p['status']) ?>"><?= e(str_replace('_',' ',$p['status'])) ?></span></td>
                <td class="mono muted"><?= formatDate($p['end_date']) ?></td>
                <td class="flex gap-8">
                    <a href="<?= url('modules/projects/edit.php?id=' . $p['id']) ?>" class="btn btn-sm">Edit</a>
                    <?php if (userCan('projects.delete')): ?>
                    <form method="post" action="<?= url('modules/projects/delete.php') ?>"
                          onsubmit="return confirm('Delete project &quot;<?= e($p['name']) ?>&quot; and all its tasks? This cannot be undone from the UI.');">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
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
