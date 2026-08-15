<?php
require_once __DIR__ . '/../../config/app.php';
requirePermission('reports.view');

$projects = (new ProjectRepository())->all();

$pageTitle = 'Reports';
$activeNav = 'reports';
require __DIR__ . '/../../includes/header.php';
?>

<div class="grid grid-3">
    <div class="card">
        <div class="card-title">Gantt Timeline</div>
        <p class="muted" style="font-size:13px; margin-bottom:16px;">Visualize task schedules across a project's timeline, colored by status.</p>
        <form method="get" action="<?= url('modules/reports/gantt.php') ?>" class="flex gap-8">
            <select name="project_id" required>
                <option value="">Choose project…</option>
                <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary btn-sm" type="submit">View</button>
        </form>
    </div>

    <div class="card">
        <div class="card-title">Burndown Chart</div>
        <p class="muted" style="font-size:13px; margin-bottom:16px;">Track remaining open tasks day-by-day against the ideal completion line.</p>
        <form method="get" action="<?= url('modules/reports/burndown.php') ?>" class="flex gap-8">
            <select name="project_id" required>
                <option value="">Choose project…</option>
                <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary btn-sm" type="submit">View</button>
        </form>
    </div>

    <div class="card">
        <div class="card-title">Team Performance</div>
        <p class="muted" style="font-size:13px; margin-bottom:16px;">Completion rate, workload, and estimated vs. actual hours per member.</p>
        <a href="<?= url('modules/reports/team_performance.php') ?>" class="btn btn-primary btn-sm">View report</a>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
