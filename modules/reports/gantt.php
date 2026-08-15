<?php
require_once __DIR__ . '/../../config/app.php';
requirePermission('reports.view');

$projectRepo = new ProjectRepository();
$taskRepo = new TaskRepository();

$projectId = (int)($_GET['project_id'] ?? 0);
$projects = $projectRepo->all();
$project = $projectRepo->findRaw($projectId);

$tasks = [];
$rangeStart = $rangeEnd = null;

if ($project) {
    $tasks = $taskRepo->rawForGantt($projectId);

    $dates = [];
    foreach ($tasks as $t) {
        if ($t['start_date']) $dates[] = $t['start_date'];
        if ($t['due_date'])   $dates[] = $t['due_date'];
    }
    if ($project['start_date']) $dates[] = $project['start_date'];
    if ($project['end_date'])   $dates[] = $project['end_date'];

    if ($dates) {
        $rangeStart = min($dates);
        $rangeEnd = max($dates);
    }
}

$statusColor = [
    'todo' => 'var(--text-faint)', 'in_progress' => 'var(--info)', 'review' => 'var(--accent)',
    'done' => 'var(--success)', 'blocked' => 'var(--danger)',
];

$pageTitle = 'Gantt Timeline';
$activeNav = 'reports';
require __DIR__ . '/../../includes/header.php';
?>

<form method="get" class="flex gap-8 mb-16">
    <select name="project_id" onchange="this.form.submit()">
        <option value="">Choose project…</option>
        <?php foreach ($projects as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $projectId == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
        <?php endforeach; ?>
    </select>
</form>

<?php if (!$project): ?>
    <div class="card"><div class="empty-state"><div class="glyph">▤</div>Choose a project to view its timeline.</div></div>
<?php elseif (!$tasks || !$rangeStart): ?>
    <div class="card"><div class="empty-state"><div class="glyph">∅</div>This project has no tasks with dates to plot yet.</div></div>
<?php else:
    $startTs = strtotime($rangeStart);
    $endTs = max(strtotime($rangeEnd), $startTs + 86400);
    $totalDays = max(1, ($endTs - $startTs) / 86400);
?>
<div class="card">
    <div class="card-title"><?= e($project['name']) ?> · <?= formatDate($rangeStart) ?> → <?= formatDate($rangeEnd) ?></div>

    <div style="display:flex; flex-direction:column; gap:10px; margin-top:8px;">
        <?php foreach ($tasks as $t):
            $tStart = $t['start_date'] ? strtotime($t['start_date']) : $startTs;
            $tEnd   = $t['due_date'] ? strtotime($t['due_date']) : $tStart + 86400;
            $offsetPct = max(0, ($tStart - $startTs) / 86400 / $totalDays * 100);
            $widthPct  = max(1.5, ($tEnd - $tStart) / 86400 / $totalDays * 100);
            $color = $statusColor[$t['status']] ?? 'var(--text-faint)';
        ?>
        <div>
            <div class="flex-between" style="font-size:12px; margin-bottom:3px;">
                <span><?= e($t['title']) ?></span>
                <span class="muted mono"><?= formatDate($t['start_date']) ?> – <?= formatDate($t['due_date']) ?></span>
            </div>
            <div style="position:relative; height:16px; background:var(--grid-line); border-radius:3px;">
                <div style="position:absolute; left:<?= $offsetPct ?>%; width:<?= $widthPct ?>%; height:100%; background:<?= $color ?>; border-radius:3px;"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="flex gap-12 mb-16" style="margin-top:20px; font-size:11px;">
        <?php foreach ($statusColor as $status => $color): ?>
            <span class="flex gap-8"><span style="width:9px;height:9px;border-radius:2px;background:<?= $color ?>;display:inline-block;"></span><?= ucfirst(str_replace('_',' ',$status)) ?></span>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
