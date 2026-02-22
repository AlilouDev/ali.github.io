<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/stock.php';

$user = requireLogin();
$pdo = db();

$totalArticles = (int)$pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn();
$todayEntries = (float)$pdo->query("SELECT COALESCE(SUM(quantity),0) FROM movements WHERE movement_type IN ('ENTREE','INITIAL','CORRECTION_PLUS') AND DATE(movement_date)=DATE('now')")->fetchColumn();
$todayExits = (float)$pdo->query("SELECT COALESCE(SUM(quantity),0) FROM movements WHERE movement_type IN ('SORTIE','CORRECTION_MINUS') AND DATE(movement_date)=DATE('now')")->fetchColumn();

$articles = $pdo->query('SELECT a.id, a.name FROM articles a ORDER BY a.name')->fetchAll();
$globalStock = 0;
foreach ($articles as $article) {
    $globalStock += currentStockForArticle((int)$article['id']);
}

$latestMovements = $pdo->query('SELECT m.*, a.name AS article_name, u.username
    FROM movements m
    JOIN articles a ON a.id = m.article_id
    LEFT JOIN users u ON u.id = m.created_by
    ORDER BY m.movement_date DESC, m.id DESC LIMIT 8')->fetchAll();

renderHeader('Dashboard', $user);
?>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card metric-card"><div class="card-body"><h6>Articles</h6><h3><?= $totalArticles ?></h3></div></div></div>
    <div class="col-md-3"><div class="card metric-card"><div class="card-body"><h6>Stock global</h6><h3><?= number_format($globalStock, 2, ',', ' ') ?></h3></div></div></div>
    <div class="col-md-3"><div class="card metric-card"><div class="card-body"><h6>Entrées du jour</h6><h3><?= number_format($todayEntries, 2, ',', ' ') ?></h3></div></div></div>
    <div class="col-md-3"><div class="card metric-card"><div class="card-body"><h6>Sorties du jour</h6><h3><?= number_format($todayExits, 2, ',', ' ') ?></h3></div></div></div>
</div>
<div class="card shadow-sm">
    <div class="card-header bg-white"><strong>Derniers mouvements</strong></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Article</th><th>Type</th><th>Quantité</th><th>Utilisateur</th></tr></thead>
            <tbody>
            <?php foreach ($latestMovements as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['movement_date']) ?></td>
                    <td><?= htmlspecialchars($row['article_name']) ?></td>
                    <td><span class="badge text-bg-secondary"><?= htmlspecialchars($row['movement_type']) ?></span></td>
                    <td><?= number_format((float)$row['quantity'], 2, ',', ' ') ?></td>
                    <td><?= htmlspecialchars((string)($row['username'] ?? 'N/A')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php renderFooter(); ?>
