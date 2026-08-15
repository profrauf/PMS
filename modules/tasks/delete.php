<?php
require_once __DIR__ . '/../../config/app.php';
requirePermission('tasks.delete');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('modules/tasks/index.php');
}
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
$user = currentUser();
$taskRepo = new TaskRepository();

$task = $taskRepo->findRaw($id);

if ($task) {
    $taskRepo->softDelete($id);
    logActivity($user['id'], 'task', $id, 'deleted', 'Deleted task "' . $task['title'] . '"');
    flash('success', 'Task deleted.');
} else {
    flash('error', 'Task not found.');
}

redirect('modules/tasks/index.php');
