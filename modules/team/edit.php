<?php
require_once __DIR__ . '/../../config/app.php';
requirePermission('team.manage');
$user = currentUser();
$userRepo = new UserRepository();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$member = ['full_name'=>'','email'=>'','role'=>'member','department'=>'','job_title'=>'','phone'=>'','status'=>'active'];

if ($id) {
    $found = $userRepo->find($id);
    if (!$found) { flash('error', 'Member not found.'); redirect('modules/team/index.php'); }
    $member = $found;
}

$error = '';
$colors = ['#E8A33D','#5B8DEF','#4CAF7D','#E1594F','#B57EDC','#4AC3D6'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'role' => $_POST['role'] ?? 'member',
        'department' => trim($_POST['department'] ?? ''),
        'job_title' => trim($_POST['job_title'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'status' => $_POST['status'] ?? 'active',
    ];
    $password = $_POST['password'] ?? '';

    if ($data['full_name'] === '' || $data['email'] === '') {
        $error = 'Name and email are required.';
    } elseif (!$id && strlen($password) < 6) {
        $error = 'Password must be at least 6 characters for a new member.';
    } else {
        if ($id) {
            $userRepo->update($id, $data);
            if ($password !== '') {
                $userRepo->updatePassword($id, password_hash($password, PASSWORD_BCRYPT));
            }
            logActivity($user['id'], 'user', $id, 'updated', 'Updated team member "' . $data['full_name'] . '"');
            flash('success', 'Member updated.');
        } else {
            $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
            $data['avatar_color'] = $colors[array_rand($colors)];
            $newId = $userRepo->create($data);
            logActivity($user['id'], 'user', $newId, 'created', 'Added team member "' . $data['full_name'] . '"');
            flash('success', 'Member added.');
        }
        redirect('modules/team/index.php');
    }
}

$pageTitle = $id ? 'Edit Member' : 'New Member';
$activeNav = 'team';
require __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:560px;">
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <?= csrfField() ?>
        <div class="form-row">
            <div class="form-group">
                <label>Full name</label>
                <input type="text" name="full_name" value="<?= e($member['full_name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($member['email']) ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <?php foreach (['admin','manager','member'] as $r): ?>
                        <option value="<?= $r ?>" <?= $member['role'] === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active" <?= $member['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $member['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Department</label>
                <input type="text" name="department" value="<?= e($member['department']) ?>">
            </div>
            <div class="form-group">
                <label>Job title</label>
                <input type="text" name="job_title" value="<?= e($member['job_title']) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?= e($member['phone']) ?>">
            </div>
            <div class="form-group">
                <label><?= $id ? 'New password (leave blank to keep)' : 'Password' ?></label>
                <input type="password" name="password" minlength="6" <?= $id ? '' : 'required' ?>>
            </div>
        </div>

        <div class="flex gap-8">
            <button type="submit" class="btn btn-primary"><?= $id ? 'Save changes' : 'Add member' ?></button>
            <a href="<?= url('modules/team/index.php') ?>" class="btn">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
