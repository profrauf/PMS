<?php
require_once __DIR__ . '/../../config/app.php';

if (isLoggedIn()) {
    redirect('modules/dashboard/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } elseif (attemptLogin($email, $password)) {
        redirect('modules/dashboard/index.php');
    } else {
        $error = 'Incorrect email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign in · <?= APP_NAME ?></title>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<div class="auth-wrap">
    <div class="card auth-card">
        <h1>Sign in</h1>
        <div class="sub">Access your engineering console.</div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <?= csrfField() ?>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Sign in</button>
        </form>

        <p class="muted" style="margin-top:18px; font-size:12px;">
            First time here? Run <span class="mono">install.php</span> once to create your admin account.
        </p>
    </div>
</div>
</body>
</html>
