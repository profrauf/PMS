<?php
/**
 * Authentication / authorization helpers.
 */

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function isLoggedIn(): bool
{
    return currentUser() !== null;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('modules/auth/login.php');
    }
}

function requireRole(array $roles): void
{
    requireLogin();
    if (!in_array(currentUser()['role'], $roles, true)) {
        http_response_code(403);
        die('You do not have permission to access this page.');
    }
}

/** Fine-grained permission check (Phase 5). Admin is always true. */
function userCan(string $permissionKey): bool
{
    $user = currentUser();
    if (!$user) {
        return false;
    }
    return (new PermissionRepository())->can($user['role'], $permissionKey);
}

function requirePermission(string $permissionKey): void
{
    requireLogin();
    if (!userCan($permissionKey)) {
        http_response_code(403);
        die('You do not have permission to perform this action.');
    }
}

function attemptLogin(string $email, string $password): bool
{
    $user = (new UserRepository())->findByEmail($email);

    if ($user && password_verify($password, $user['password_hash'])) {
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        return true;
    }
    return false;
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
}
