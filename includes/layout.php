<?php

declare(strict_types=1);

function renderHeader(string $title, ?array $user = null): void
{
    $current = basename($_SERVER['PHP_SELF']);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="assets/css/app.css">
    </head>
    <body>
    <div class="container-fluid">
        <div class="row min-vh-100">
            <?php if ($user): ?>
                <aside class="col-lg-2 bg-dark text-white p-3 sidebar">
                    <h4 class="mb-4">StockFlow</h4>
                    <nav class="nav flex-column gap-2">
                        <a class="nav-link text-white <?= $current === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">Dashboard</a>
                        <a class="nav-link text-white <?= $current === 'articles.php' ? 'active' : '' ?>" href="articles.php">Articles</a>
                        <a class="nav-link text-white <?= $current === 'movements.php' ? 'active' : '' ?>" href="movements.php">Mouvements</a>
                        <a class="nav-link text-white <?= $current === 'history.php' ? 'active' : '' ?>" href="history.php">Historique</a>
                        <a class="nav-link text-white <?= $current === 'reports.php' ? 'active' : '' ?>" href="reports.php">Rapports</a>
                        <a class="nav-link text-white <?= $current === 'settings.php' ? 'active' : '' ?>" href="settings.php">Configuration</a>
                        <a class="nav-link text-danger" href="logout.php">Déconnexion</a>
                    </nav>
                </aside>
                <main class="col-lg-10 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0"><?= htmlspecialchars($title) ?></h2>
                        <div class="text-end">
                            <strong><?= htmlspecialchars($user['username']) ?></strong><br>
                            <small><?= htmlspecialchars((string)($user['role_name'] ?? 'N/A')) ?></small>
                        </div>
                    </div>
            <?php else: ?>
                <main class="col-12 p-0">
            <?php endif;
}

function renderFooter(): void
{
    ?>
                </main>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/app.js"></script>
    </body>
    </html>
    <?php
}
