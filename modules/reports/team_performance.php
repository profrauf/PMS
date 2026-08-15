<?php
require_once __DIR__ . '/../../config/app.php';
requirePermission('reports.view');

$rows = (new UserRepository())->performanceReport();

if (($_GET['export'] ?? '') === 'csv') {
    $csvRows = [];
    foreach ($rows as $r) {
        $rate = $r['total_tasks'] > 0 ? round($r['done_tasks'] / $r['total_tasks'] * 100, 1) : 0;
        $csvRows[] = [
            $r['full_name'], $r['department'], $r['job_title'],
            $r['total_tasks'], $r['done_tasks'], $r['overdue_tasks'], $rate . '%',
            $r['avg_estimated'] !== null ? round($r['avg_estimated'], 1) : '',
            $r['avg_actual'] !== null ? round($r['avg_actual'], 1) : '',
        ];
    }
    exportCsv(
        'team-performance-' . date('Y-m-d') . '.csv',
        ['Name', 'Department', 'Job Title', 'Total Tasks', 'Done', 'Overdue', 'Completion Rate', 'Avg Estimated Hrs', 'Avg Actual Hrs'],
        $csvRows
    );
}

$pageTitle = 'Team Performance';
$activeNav = 'reports';
require __DIR__ . '/../../includes/header.php';
?>

<div class="flex-between mb-16">
    <div class="muted" style="font-size:13px;"><?= count($rows) ?> team members</div>
    <a href="?export=csv" class="btn btn-sm">Export CSV</a>
</div>

<div class="card">
    <table>
        <thead>
        <tr><th>Member</th><th>Total</th><th>Done</th><th>Overdue</th><th>Completion</th><th>Avg Est. Hrs</th><th>Avg Actual Hrs</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r):
            $rate = $r['total_tasks'] > 0 ? round($r['done_tasks'] / $r['total_tasks'] * 100) : 0;
        ?>
            <tr>
                <td>
                    <div style="font-weight:600;"><?= e($r['full_name']) ?></div>
                    <div class="muted" style="font-size:11px;"><?= e($r['job_title'] ?: '—') ?></div>
                </td>
                <td><?= (int)$r['total_tasks'] ?></td>
                <td><?= (int)$r['done_tasks'] ?></td>
                <td><?= $r['overdue_tasks'] > 0 ? '<span class="badge badge-priority-critical">'.(int)$r['overdue_tasks'].'</span>' : '0' ?></td>
                <td style="width:140px;">
                    <div class="flex gap-8">
                        <div class="progress-track" style="flex:1;"><div class="progress-fill" style="width:<?= $rate ?>%"></div></div>
                        <span class="mono" style="font-size:11px;"><?= $rate ?>%</span>
                    </div>
                </td>
                <td class="mono muted"><?= $r['avg_estimated'] !== null ? round($r['avg_estimated'],1) : '—' ?></td>
                <td class="mono muted"><?= $r['avg_actual'] !== null ? round($r['avg_actual'],1) : '—' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
