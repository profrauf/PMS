<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();
$user = currentUser();

$projectRepo = new ProjectRepository();
$userRepo = new UserRepository();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
requirePermission($id ? 'projects.edit' : 'projects.create');
$project = ['name'=>'','code'=>'','description'=>'','project_type'=>'','status'=>'planning','priority'=>'medium','progress'=>0,'start_date'=>'','end_date'=>'','owner_id'=>null];

if ($id) {
    $found = $projectRepo->findRaw($id);
    if (!$found) { flash('error', 'Project not found.'); redirect('modules/projects/index.php'); }
    $project = $found;
}

$users = $userRepo->activeList();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'code' => trim($_POST['code'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'project_type' => trim($_POST['project_type'] ?? ''),
        'status' => $_POST['status'] ?? 'planning',
        'priority' => $_POST['priority'] ?? 'medium',
        'progress' => (int)($_POST['progress'] ?? 0),
        'start_date' => $_POST['start_date'] ?: null,
        'end_date' => $_POST['end_date'] ?: null,
        'owner_id' => $_POST['owner_id'] ?: null,
    ];

    if ($data['name'] === '' || $data['code'] === '') {
        $error = 'Name and code are required.';
    } else {
        if ($id) {
            $projectRepo->update($id, $data);
            logActivity($user['id'], 'project', $id, 'updated', 'Updated project "' . $data['name'] . '"');
            flash('success', 'Project updated.');
        } else {
            $data['created_by'] = $user['id'];
            $newId = $projectRepo->create($data);
            if ($data['owner_id']) {
                $projectRepo->addMember($newId, (int)$data['owner_id'], 'Owner');
            }
            logActivity($user['id'], 'project', $newId, 'created', 'Created project "' . $data['name'] . '"');
            flash('success', 'Project created.');
        }
        redirect('modules/projects/index.php');
    }
}

$pageTitle = $id ? 'Edit Project' : 'New Project';
$activeNav = 'projects';
require __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:640px;">
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <?= csrfField() ?>
        <div class="form-row">
            <div class="form-group">
                <label>Project name</label>
                <input type="text" name="name" value="<?= e($project['name']) ?>" required>
            </div>
            <div class="form-group" style="max-width:160px;">
                <label>Code</label>
                <input type="text" name="code" value="<?= e($project['code']) ?>" placeholder="PRJ-002" required>
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description"><?= e($project['description']) ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Project type</label>
                <input type="text" name="project_type" value="<?= e($project['project_type']) ?>" placeholder="software, construction, research…">
            </div>
            <div class="form-group">
                <label>Owner</label>
                <select name="owner_id">
                    <option value="">— Unassigned —</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $project['owner_id'] == $u['id'] ? 'selected' : '' ?>><?= e($u['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <?php foreach (['planning','active','on_hold','completed','cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= $project['status'] === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Priority</label>
                <select name="priority">
                    <?php foreach (['low','medium','high','critical'] as $p): ?>
                        <option value="<?= $p ?>" <?= $project['priority'] === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Progress (%)</label>
                <input type="number" name="progress" min="0" max="100" value="<?= (int)$project['progress'] ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Start date</label>
                <input type="date" name="start_date" value="<?= e($project['start_date']) ?>">
            </div>
            <div class="form-group">
                <label>End date</label>
                <input type="date" name="end_date" value="<?= e($project['end_date']) ?>">
            </div>
        </div>

        <div class="flex gap-8">
            <button type="submit" class="btn btn-primary"><?= $id ? 'Save changes' : 'Create project' ?></button>
            <a href="<?= url('modules/projects/index.php') ?>" class="btn">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
