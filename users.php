<?php
require_once __DIR__ . '/app/functions.php';
$title = 'Configuration';
require __DIR__ . '/partials/header.php';
require_privilege($user, 'users');

$allPrivs = ['dashboard','articles','movements','history','users','roles','reports','settings'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_role'])) {
        $priv = json_encode($_POST['privileges'] ?? []);
        db()->prepare('INSERT INTO roles(name, privileges) VALUES (?,?)')->execute([trim($_POST['name']), $priv]);
        flash('ok', 'Rôle créé');
    } elseif (isset($_POST['create_user'])) {
        db()->prepare('INSERT INTO users(username, password, role_id, active) VALUES (?,?,?,1)')->execute([
            trim($_POST['username']),
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            (int)$_POST['role_id']
        ]);
        flash('ok', 'Utilisateur créé');
    } elseif (isset($_POST['save_report'])) {
        db()->prepare('UPDATE report_settings SET company_name=?, stock_report_title=?, card_report_title=?, logo_url=? WHERE id=1')->execute([
            trim($_POST['company_name']), trim($_POST['stock_report_title']), trim($_POST['card_report_title']), trim($_POST['logo_url'])
        ]);
        flash('ok', 'En-têtes de rapport mis à jour');
    }
    header('Location: /users.php'); exit;
}

$roles = db()->query('SELECT * FROM roles ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$users = db()->query('SELECT u.id,u.username,u.active,r.name role_name FROM users u JOIN roles r ON r.id=u.role_id ORDER BY u.id DESC')->fetchAll(PDO::FETCH_ASSOC);
$settings = db()->query('SELECT * FROM report_settings WHERE id=1')->fetch(PDO::FETCH_ASSOC);
?>
<div class="row g-3">
<div class="col-lg-4"><div class="card"><div class="card-body"><h6>Nouveau rôle</h6><form method="post"><input type="hidden" name="create_role" value="1"><input name="name" class="form-control mb-2" placeholder="Nom rôle" required><?php foreach($allPrivs as $p): ?><div class="form-check"><input class="form-check-input" type="checkbox" name="privileges[]" value="<?= $p ?>" id="p-<?= $p ?>"><label class="form-check-label" for="p-<?= $p ?>"><?= $p ?></label></div><?php endforeach; ?><button class="btn btn-primary mt-2">Créer rôle</button></form></div></div></div>
<div class="col-lg-4"><div class="card"><div class="card-body"><h6>Nouvel utilisateur</h6><form method="post"><input type="hidden" name="create_user" value="1"><input name="username" class="form-control mb-2" placeholder="Username" required><input name="password" class="form-control mb-2" placeholder="Password" required><select name="role_id" class="form-select mb-2"><?php foreach($roles as $r): ?><option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option><?php endforeach; ?></select><button class="btn btn-success">Créer utilisateur</button></form><hr><h6>Utilisateurs</h6><ul class="list-group"><?php foreach($users as $u): ?><li class="list-group-item d-flex justify-content-between"><span><?= htmlspecialchars($u['username']) ?></span><span class="badge bg-secondary"><?= htmlspecialchars($u['role_name']) ?></span></li><?php endforeach; ?></ul></div></div></div>
<div class="col-lg-4"><div class="card"><div class="card-body"><h6>Paramètres rapport</h6><form method="post"><input type="hidden" name="save_report" value="1"><input name="company_name" class="form-control mb-2" value="<?= htmlspecialchars($settings['company_name']) ?>" placeholder="Société"><input name="stock_report_title" class="form-control mb-2" value="<?= htmlspecialchars($settings['stock_report_title']) ?>"><input name="card_report_title" class="form-control mb-2" value="<?= htmlspecialchars($settings['card_report_title']) ?>"><input name="logo_url" class="form-control mb-2" value="<?= htmlspecialchars($settings['logo_url']) ?>" placeholder="URL logo"><button class="btn btn-outline-primary">Sauvegarder</button></form></div></div></div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
