<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

$user = requireLogin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user']) && (int)$user['is_admin'] === 1) {
        $stmt = $pdo->prepare('INSERT INTO users(username, password_hash, role_id, is_admin) VALUES(:username, :password_hash, :role_id, :is_admin)');
        $stmt->execute([
            ':username' => trim($_POST['username'] ?? ''),
            ':password_hash' => password_hash((string)($_POST['password'] ?? '123456'), PASSWORD_DEFAULT),
            ':role_id' => (int)($_POST['role_id'] ?? 0) ?: null,
            ':is_admin' => isset($_POST['is_admin']) ? 1 : 0,
        ]);
    }

    if (isset($_POST['link_privilege']) && (int)$user['is_admin'] === 1) {
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO role_privileges(role_id, privilege_id) VALUES(:role_id, :privilege_id)');
        $stmt->execute([
            ':role_id' => (int)$_POST['role_id'],
            ':privilege_id' => (int)$_POST['privilege_id'],
        ]);
    }

    header('Location: settings.php');
    exit;
}

$roles = $pdo->query('SELECT * FROM roles ORDER BY name')->fetchAll();
$privileges = $pdo->query('SELECT * FROM privileges ORDER BY name')->fetchAll();
$users = $pdo->query('SELECT u.username, u.is_admin, r.name AS role_name FROM users u LEFT JOIN roles r ON r.id = u.role_id ORDER BY u.created_at DESC')->fetchAll();
$map = $pdo->query('SELECT r.name AS role_name, p.name AS privilege_name
                    FROM role_privileges rp
                    JOIN roles r ON r.id = rp.role_id
                    JOIN privileges p ON p.id = rp.privilege_id
                    ORDER BY r.name, p.name')->fetchAll();

renderHeader('Configuration utilisateurs / rôles / privilèges', $user);
?>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm"><div class="card-body">
            <h5>Utilisateurs</h5>
            <?php if ((int)$user['is_admin'] === 1): ?>
            <form method="post" class="row g-2 mb-3">
                <div class="col-md-4"><input class="form-control" name="username" placeholder="Username" required></div>
                <div class="col-md-3"><input class="form-control" name="password" placeholder="Password" required></div>
                <div class="col-md-3"><select class="form-select" name="role_id"><?php foreach ($roles as $role): ?><option value="<?= (int)$role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2 form-check mt-2"><input class="form-check-input" type="checkbox" name="is_admin" id="is_admin"><label class="form-check-label" for="is_admin">Admin</label></div>
                <div class="col-12"><button class="btn btn-primary" name="add_user">Créer utilisateur</button></div>
            </form>
            <?php endif; ?>
            <ul class="list-group">
                <?php foreach ($users as $item): ?>
                    <li class="list-group-item d-flex justify-content-between"><span><?= htmlspecialchars($item['username']) ?> (<?= htmlspecialchars((string)($item['role_name'] ?? '-')) ?>)</span><span><?= (int)$item['is_admin'] ? 'Admin' : 'User' ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm"><div class="card-body">
            <h5>Mappage rôles et privilèges</h5>
            <?php if ((int)$user['is_admin'] === 1): ?>
            <form method="post" class="row g-2 mb-3">
                <div class="col-md-5"><select class="form-select" name="role_id"><?php foreach ($roles as $role): ?><option value="<?= (int)$role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-5"><select class="form-select" name="privilege_id"><?php foreach ($privileges as $privilege): ?><option value="<?= (int)$privilege['id'] ?>"><?= htmlspecialchars($privilege['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><button class="btn btn-outline-primary w-100" name="link_privilege">Lier</button></div>
            </form>
            <?php endif; ?>
            <ul class="list-group small">
                <?php foreach ($map as $item): ?>
                    <li class="list-group-item"><?= htmlspecialchars($item['role_name']) ?> → <?= htmlspecialchars($item['privilege_name']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div></div>
    </div>
</div>
<?php renderFooter(); ?>
