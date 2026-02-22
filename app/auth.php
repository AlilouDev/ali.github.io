<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/init_db.php';

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT u.*, r.name as role_name, r.privileges FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ? AND u.active = 1');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        unset($_SESSION['user_id']);
        return null;
    }
    $user['privileges'] = json_decode($user['privileges'], true) ?: [];
    return $user;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        header('Location: /index.php');
        exit;
    }
    return $user;
}

function can(array $user, string $priv): bool
{
    return $user['role_name'] === 'admin' || in_array($priv, $user['privileges'], true);
}

function require_privilege(array $user, string $priv): void
{
    if (!can($user, $priv)) {
        http_response_code(403);
        echo 'Accès refusé';
        exit;
    }
}
