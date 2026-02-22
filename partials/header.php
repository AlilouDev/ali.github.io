<?php
require_once __DIR__ . '/../app/auth.php';
$user = require_login();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'Stock App') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f6f8fb; }
    .navbar-brand { font-weight:700; }
    .card { border:0; box-shadow: 0 4px 20px rgba(0,0,0,.05); border-radius: 1rem; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand" href="/dashboard.php">StockFlow</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="/dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="/articles.php">Articles</a></li>
        <li class="nav-item"><a class="nav-link" href="/movements.php">Mouvements</a></li>
        <li class="nav-item"><a class="nav-link" href="/history.php">Historique</a></li>
        <li class="nav-item"><a class="nav-link" href="/reports.php">États</a></li>
        <?php if (can($user, 'users')): ?>
          <li class="nav-item"><a class="nav-link" href="/users.php">Configuration</a></li>
        <?php endif; ?>
      </ul>
      <span class="text-white small me-3">Connecté: <?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['role_name']) ?>)</span>
      <a href="/logout.php" class="btn btn-light btn-sm">Déconnexion</a>
    </div>
  </div>
</nav>
<div class="container py-4">
<?php if ($msg = flash('ok')): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('err')): ?><div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
