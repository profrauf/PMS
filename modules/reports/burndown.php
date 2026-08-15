<?php
require_once __DIR__ . '/../../config/app.php';
requirePermission('reports.view');

$projectRepo = new ProjectRepository();
$taskRepo = new TaskRepository();

$projectId = (int)($_GET['project_id'] ?? 0);
$projects = $projectRepo->all();
$project = $projectRepo->findRaw($projectId);

$pageTitle = 'Burndown Chart';
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
    <div class="card"><div class="empty-state"><div class="glyph">▤</div>Choose a project to view its burndown.</div></div>
<?php else:
    $tasks = $taskRepo->rawForBurndown($projectId);
    $total = count($tasks);

    $rangeStartTs = $project['start_date'] ? strtotime($project['start_date']) : ($tasks ? strtotime(min(array_column($tasks, 'created_at'))) : strtotime('-7 days'));
    $rangeEndTs = $project['end_date'] ? strtotime($project['end_date']) : strtotime('today');
    $rangeEndTs = max($rangeEndTs, strtotime('today'));
    $days = max(1, (int)round(($rangeEndTs - $rangeStartTs) / 86400));

    $actualPoints = [];
    $idealPoints = [];
    for ($d = 0; $d <= $days; $d++) {
        $dayTs = $rangeStartTs + $d * 86400;
        $completedByDay = 0;
        foreach ($tasks as $t) {
            if ($t['completed_at'] && strtotime($t['completed_at']) <= $dayTs) $completedByDay++;
        }
        $remaining = $total - $completedByDay;
        $actualPoints[] = $remaining;
        $idealPoints[] = $total > 0 ? round($total - ($total / $days) * $d, 1) : 0;
    }

    // Build SVG coordinates
    $w = 760; $h = 280; $padL = 36; $padB = 24; $padT = 10; $padR = 10;
    $plotW = $w - $padL - $padR; $plotH = $h - $padT - $padB;
    $maxY = max(1, $total);

    $toXY = function($i, $val) use ($days, $plotW, $plotH, $padL, $padT, $maxY) {
        $x = $padL + ($days > 0 ? ($i / $days) * $plotW : 0);
        $y = $padT + $plotH - ($val / $maxY) * $plotH;
        return [$x, $y];
    };

    $actualPath = ''; $idealPath = '';
    foreach ($actualPoints as $i => $v) { [$x,$y] = $toXY($i, max(0,$v)); $actualPath .= ($i===0?'M':'L') . round($x,1) . ',' . round($y,1) . ' '; }
    foreach ($idealPoints as $i => $v) { [$x,$y] = $toXY($i, max(0,$v)); $idealPath  .= ($i===0?'M':'L') . round($x,1) . ',' . round($y,1) . ' '; }
?>
<div class="card">
    <div class="card-title"><?= e($project['name']) ?> · Remaining tasks over time (total: <?= $total ?>)</div>

    <?php if ($total === 0): ?>
        <div class="empty-state"><div class="glyph">∅</div>No tasks in this project yet.</div>
    <?php else: ?>
    <svg viewBox="0 0 <?= $w ?> <?= $h ?>" style="width:100%; height:auto;">
        <?php for ($gy = 0; $gy <= 4; $gy++):
            $val = round($maxY - ($maxY/4)*$gy);
            $y = $padT + ($plotH/4)*$gy;
        ?>
            <line x1="<?= $padL ?>" y1="<?= $y ?>" x2="<?= $w - $padR ?>" y2="<?= $y ?>" stroke="var(--grid-line)" stroke-width="1"/>
            <text x="4" y="<?= $y + 4 ?>" fill="var(--text-faint)" font-size="10" font-family="JetBrains Mono, monospace"><?= $val ?></text>
        <?php endfor; ?>

        <path d="<?= $idealPath ?>" fill="none" stroke="var(--text-faint)" stroke-width="1.5" stroke-dasharray="4,4"/>
        <path d="<?= $actualPath ?>" fill="none" stroke="var(--accent)" stroke-width="2.5"/>
    </svg>

    <div class="flex gap-12" style="font-size:11px; margin-top:6px;">
        <span class="flex gap-8"><span style="width:14px;height:2px;background:var(--accent);display:inline-block;"></span>Actual remaining</span>
        <span class="flex gap-8"><span style="width:14px;height:2px;background:var(--text-faint);display:inline-block; border-top:2px dashed var(--text-faint);"></span>Ideal pace</span>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
