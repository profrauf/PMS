<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();

$projectRepo = new ProjectRepository();
$taskRepo = new TaskRepository();
$userRepo = new UserRepository();
$activityRepo = new ActivityRepository();

$stats = [
    'active_projects' => $projectRepo->countByStatus('active'),
    'open_tasks'       => $taskRepo->countByStatusNot('done'),
    'done_tasks'       => $taskRepo->countByStatus('done'),
    'team_members'     => $userRepo->countActive(),
];

$activeProjects = $projectRepo->recentlyUpdated(5);
$upcomingTasks = $taskRepo->upcoming(6);
$activity = $activityRepo->recent(8);

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/../../includes/header.php';
?>

<div class="grid grid-4 mb-24">
    <div class="card">
        <div class="stat-value"><?= (int)$stats['active_projects'] ?></div>
        <div class="stat-label">Active Projects</div>
    </div>
    <div class="card">
        <div class="stat-value"><?= (int)$stats['open_tasks'] ?></div>
        <div class="stat-label">Open Tasks</div>
    </div>
    <div class="card">
        <div class="stat-value"><?= (int)$stats['done_tasks'] ?></div>
        <div class="stat-label">Completed Tasks</div>
    </div>
    <div class="card">
        <div class="stat-value"><?= (int)$stats['team_members'] ?></div>
        <div class="stat-label">Team Members</div>
    </div>
</div>

<div class="grid grid-2">
    <div>
        <div class="card mb-24">
            <div class="flex-between mb-16">
                <div class="card-title" style="margin-bottom:0;">Active Projects</div>
                <a href="<?= url('modules/projects/index.php') ?>" class="muted" style="font-size:12px;">View all →</a>
            </div>
            <?php if (!$activeProjects): ?>
                <div class="empty-state"><div class="glyph">∅</div>No projects yet.</div>
            <?php else: ?>
            <table>
                <thead><tr><th>Project</th><th>Owner</th><th>Progress</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($activeProjects as $p): ?>
                    <tr onclick="location.href='<?= url('modules/projects/view.php?id=' . $p['id']) ?>'" style="cursor:pointer;">
                        <td>
                            <div style="font-weight:600;"><?= e($p['name']) ?></div>
                            <div class="muted mono" style="font-size:11px;"><?= e($p['code']) ?> · <?= (int)$p['task_count'] ?> tasks</div>
                        </td>
                        <td><?= e($p['owner_name'] ?? '—') ?></td>
                        <td style="width:120px;">
                            <div class="progress-track"><div class="progress-fill" style="width:<?= (int)$p['progress'] ?>%"></div></div>
                        </td>
                        <td><span class="<?= statusBadgeClass($p['status']) ?>"><?= e(str_replace('_',' ',$p['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-title">Upcoming Tasks</div>
            <?php if (!$upcomingTasks): ?>
                <div class="empty-state"><div class="glyph">∅</div>No upcoming tasks.</div>
            <?php else: ?>
            <table>
                <thead><tr><th>Task</th><th>Project</th><th>Assignee</th><th>Due</th><th>Priority</th></tr></thead>
                <tbody>
                <?php foreach ($upcomingTasks as $t): ?>
                    <tr onclick="location.href='<?= url('modules/tasks/view.php?id=' . $t['id']) ?>'" style="cursor:pointer;">
                        <td><?= e($t['title']) ?></td>
                        <td class="muted"><?= e($t['project_name']) ?></td>
                        <td><?= e($t['assignee_name'] ?? '—') ?></td>
                        <td class="mono"><?= formatDate($t['due_date']) ?></td>
                        <td><span class="<?= priorityBadgeClass($t['priority']) ?>"><?= e($t['priority']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" style="align-self:start;">
        <div class="card-title">Activity Log</div>
        <?php if (!$activity): ?>
            <div class="empty-state"><div class="glyph">∅</div>No activity yet.</div>
        <?php else: ?>
        <ul style="display:flex; flex-direction:column; gap:14px;">
            <?php foreach ($activity as $a): ?>
                <li style="border-left:2px solid var(--border); padding-left:10px;">
                    <div style="font-size:13px;"><?= e($a['description']) ?></div>
                    <div class="muted mono" style="font-size:10.5px; margin-top:2px;">
                        <?= e($a['full_name'] ?? 'System') ?> · <?= date('M d, H:i', strtotime($a['created_at'])) ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
