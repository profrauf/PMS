<?php
/**
 * One-time installer.
 * 1. Import database/schema.sql first (via phpMyAdmin or CLI).
 * 2. Open this file in your browser: http://localhost/pms/install.php
 * 3. It creates your admin account + a sample project, then blocks itself.
 */
require_once __DIR__ . '/config/database.php';

$pdo = db();
$existing = $pdo->query('SELECT COUNT(*) AS c FROM users')->fetch();

$done = false;
$error = '';

if ($existing['c'] > 0) {
    $error = 'Setup has already run — at least one user already exists in the database. '
           . 'For security, delete or rename install.php now.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || strlen($password) < 6) {
        $error = 'Please fill in all fields. Password must be at least 6 characters.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $pdo->prepare(
            'INSERT INTO users (full_name, email, password_hash, role, department, job_title)
             VALUES (:n, :e, :h, "admin", "Management", "Administrator")'
        )->execute(['n' => $name, 'e' => $email, 'h' => $hash]);

        $adminId = (int) $pdo->lastInsertId();

        $pdo->prepare(
            "INSERT INTO projects (name, code, description, project_type, status, priority, progress, start_date, end_date, owner_id, created_by)
             VALUES ('Sample Project', 'PRJ-001', 'A sample project to help you explore the system.',
                     'software', 'active', 'medium', 25, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY), :o, :c)"
        )->execute(['o' => $adminId, 'c' => $adminId]);

        $projectId = (int) $pdo->lastInsertId();

        $pdo->prepare(
            "INSERT INTO phases (project_id, name, sequence_order, status) VALUES
             (:p, 'Planning', 1, 'completed'),
             (:p, 'Execution', 2, 'in_progress'),
             (:p, 'Closure', 3, 'not_started')"
        )->execute(['p' => $projectId]);

        $phaseStmt = $pdo->prepare("SELECT id FROM phases WHERE project_id = :p AND sequence_order = 2");
        $phaseStmt->execute(['p' => $projectId]);
        $execPhaseId = $phaseStmt->fetchColumn();

        $pdo->prepare(
            "INSERT INTO tasks (project_id, phase_id, title, description, priority, status, progress, assigned_to, created_by, due_date) VALUES
             (:p, :ph, 'Define project scope', 'Draft and approve the scope document.', 'high', 'done', 100, :u, :u, CURDATE()),
             (:p, :ph, 'Set up development environment', 'Prepare local environment and repositories.', 'medium', 'in_progress', 50, :u, :u, DATE_ADD(CURDATE(), INTERVAL 5 DAY))"
        )->execute(['p' => $projectId, 'ph' => $execPhaseId, 'u' => $adminId]);

        $pdo->prepare(
            "INSERT INTO project_members (project_id, user_id, role_in_project) VALUES (:p, :u, 'Owner')"
        )->execute(['p' => $projectId, 'u' => $adminId]);

        logActivitySimple($pdo, $adminId, 'project', $projectId, 'created', 'Created project "Sample Project"');

        $done = true;
    }
}

function logActivitySimple(PDO $pdo, int $userId, string $type, int $id, string $action, string $desc): void
{
    $pdo->prepare(
        'INSERT INTO activity_log (user_id, entity_type, entity_id, action, description) VALUES (:u,:t,:i,:a,:d)'
    )->execute(['u' => $userId, 't' => $type, 'i' => $id, 'a' => $action, 'd' => $desc]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Setup · PMS</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="card auth-card">
        <h1>Initial setup</h1>
        <div class="sub">Create your administrator account.</div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($done): ?>
            <div class="alert alert-success">Account created. Sample project loaded.</div>
            <a href="modules/auth/login.php" class="btn btn-primary btn-block">Go to sign in</a>
        <?php elseif ($existing['c'] == 0): ?>
            <form method="post">
                <div class="form-group">
                    <label>Full name</label>
                    <input type="text" name="full_name" required autofocus>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Create admin account</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
