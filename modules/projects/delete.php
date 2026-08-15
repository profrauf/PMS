<?php
require_once __DIR__ . '/../../config/app.php';
requirePermission('projects.delete');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('modules/projects/index.php');
}
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
$user = currentUser();
$projectRepo = new ProjectRepository();

$project = $projectRepo->findRaw($id);

if ($project) {
    $projectRepo->softDelete($id);
    logActivity($user['id'], 'project', $id, 'deleted', 'Deleted project "' . $project['name'] . '"');
    flash('success', 'Project deleted.');
} else {
    flash('error', 'Project not found.');
}

redirect('modules/projects/index.php');
