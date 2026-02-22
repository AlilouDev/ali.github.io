<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

if (currentUser()) {
    header('Location: dashboard.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if (login($username, $password)) {
        header('Location: dashboard.php');
        exit;
    }

    $error = 'Identifiants invalides. Test: admin/admin';
}

renderHeader('Connexion');
?>
<div class="login-wrap d-flex align-items-center justify-content-center">
    <div class="card shadow-lg border-0 login-card">
        <div class="card-body p-4">
            <h3 class="text-center mb-3">Gestion de Stock</h3>
            <p class="text-center text-muted">Connexion sécurisée</p>
            <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Utilisateur</label>
                    <input name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100">Se connecter</button>
            </form>
        </div>
    </div>
</div>
<?php renderFooter(); ?>
