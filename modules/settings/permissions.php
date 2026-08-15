<?php
require_once __DIR__ . '/../../config/app.php';
requirePermission('settings.manage');

$permRepo = new PermissionRepository();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $catalogue = $permRepo->catalogue();
    $roles = ['admin', 'manager', 'member'];

    foreach ($roles as $role) {
        if ($role === 'admin') continue; // admin is always full-access, not editable
        foreach ($catalogue as $perm) {
            $fieldName = $role . '__' . $perm['key'];
            $allowed = isset($_POST[$fieldName]);
            $permRepo->setPermission($role, $perm['key'], $allowed);
        }
    }
    logActivity(currentUser()['id'], 'settings', null, 'updated', 'Updated role permissions matrix');
    flash('success', 'Permissions updated.');
    redirect('modules/settings/permissions.php');
}

$catalogue = $permRepo->catalogue();
$matrix = $permRepo->fullMatrix();
$byCategory = [];
foreach ($catalogue as $perm) {
    $byCategory[$perm['category']][] = $perm;
}

$pageTitle = 'Permissions';
$activeNav = 'settings';
require __DIR__ . '/../../includes/header.php';
?>

<p class="muted mb-16" style="font-size:13px;">
    Admin always has full access and isn't shown here. Toggle what Managers and Members can do across the system.
</p>

<form method="post">
    <?= csrfField() ?>

    <?php foreach ($byCategory as $category => $perms): ?>
    <div class="card mb-24">
        <div class="card-title"><?= e($category) ?></div>
        <table>
            <thead><tr><th>Permission</th><th style="width:100px; text-align:center;">Manager</th><th style="width:100px; text-align:center;">Member</th></tr></thead>
            <tbody>
            <?php foreach ($perms as $perm): ?>
                <tr>
                    <td><?= e($perm['label']) ?></td>
                    <td style="text-align:center;">
                        <input type="checkbox" name="manager__<?= e($perm['key']) ?>"
                               <?= ($matrix['manager'][$perm['key']] ?? false) ? 'checked' : '' ?>>
                    </td>
                    <td style="text-align:center;">
                        <input type="checkbox" name="member__<?= e($perm['key']) ?>"
                               <?= ($matrix['member'][$perm['key']] ?? false) ? 'checked' : '' ?>>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-primary">Save permissions</button>
</form>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
