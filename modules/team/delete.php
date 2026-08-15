<?php
require_once __DIR__ . '/../../config/app.php';
requirePermission('team.manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('modules/team/index.php');
}
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
$user = currentUser();
$userRepo = new UserRepository();
$taskRepo = new TaskRepository();

if ($id === (int)$user['id']) {
    flash('error', 'You cannot delete your own account while logged in.');
    redirect('modules/team/index.php');
}

$member = $userRepo->find($id);

if (!$member) {
    flash('error', 'Member not found.');
    redirect('modules/team/index.php');
}

if ($member['role'] === 'admin' && $userRepo->countActiveAdmins() <= 1) {
    flash('error', 'Cannot delete the last remaining admin account.');
    redirect('modules/team/index.php');
}

$userRepo->softDelete($id);
$taskRepo->unassignFromUser($id);
logActivity($user['id'], 'user', $id, 'deleted', 'Removed team member "' . $member['full_name'] . '"');
flash('success', 'Member removed.');

redirect('modules/team/index.php');
