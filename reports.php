<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/stock.php';

$user = requireLogin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (int)$user['is_admin'] === 1) {
    $reportKey = (string)($_POST['report_key'] ?? '');
    $title = trim($_POST['report_title'] ?? '');
    $subtitle = trim($_POST['report_subtitle'] ?? '');

    $logoPath = $_POST['existing_logo'] ?? '';
    if (!empty($_FILES['logo']['name']) && is_uploaded_file($_FILES['logo']['tmp_name'])) {
        $filename = 'logo_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $_FILES['logo']['name']);
        $dest = __DIR__ . '/uploads/' . $filename;
        move_uploaded_file($_FILES['logo']['tmp_name'], $dest);
        $logoPath = 'uploads/' . $filename;
    }

    $stmt = $pdo->prepare('UPDATE report_templates SET report_title=:title, report_subtitle=:subtitle, logo_path=:logo WHERE report_key=:key');
    $stmt->execute([':title' => $title, ':subtitle' => $subtitle, ':logo' => $logoPath, ':key' => $reportKey]);
    header('Location: reports.php');
    exit;
}

$templates = [];
foreach ($pdo->query('SELECT * FROM report_templates') as $tpl) {
    $templates[$tpl['report_key']] = $tpl;
}

$articles = $pdo->query('SELECT id, name, sku FROM articles ORDER BY name')->fetchAll();
$movements = $pdo->query('SELECT m.*, a.name AS article_name FROM movements m JOIN articles a ON a.id=m.article_id ORDER BY m.movement_date DESC, m.id DESC LIMIT 50')->fetchAll();

renderHeader('Rapports', $user);
?>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm"><div class="card-body report-box">
            <?php $etat = $templates['etat_stock'] ?? null; ?>
            <?php if ($etat && !empty($etat['logo_path'])): ?><img src="<?= htmlspecialchars($etat['logo_path']) ?>" class="report-logo" alt="logo"><?php endif; ?>
            <h4><?= htmlspecialchars($etat['report_title'] ?? 'Etat du stock') ?></h4>
            <p class="text-muted"><?= htmlspecialchars($etat['report_subtitle'] ?? '') ?></p>
            <table class="table table-sm">
                <thead><tr><th>Article</th><th>SKU</th><th>Stock</th></tr></thead>
                <tbody>
                <?php foreach ($articles as $article): ?>
                    <tr><td><?= htmlspecialchars($article['name']) ?></td><td><?= htmlspecialchars((string)$article['sku']) ?></td><td><?= number_format(currentStockForArticle((int)$article['id']), 2, ',', ' ') ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm"><div class="card-body report-box">
            <?php $fiche = $templates['fiche_stock'] ?? null; ?>
            <?php if ($fiche && !empty($fiche['logo_path'])): ?><img src="<?= htmlspecialchars($fiche['logo_path']) ?>" class="report-logo" alt="logo"><?php endif; ?>
            <h4><?= htmlspecialchars($fiche['report_title'] ?? 'Fiche de stock') ?></h4>
            <p class="text-muted"><?= htmlspecialchars($fiche['report_subtitle'] ?? '') ?></p>
            <table class="table table-sm">
                <thead><tr><th>Date</th><th>Article</th><th>Type</th><th>Qté</th></tr></thead>
                <tbody>
                <?php foreach ($movements as $movement): ?>
                    <tr><td><?= htmlspecialchars($movement['movement_date']) ?></td><td><?= htmlspecialchars($movement['article_name']) ?></td><td><?= htmlspecialchars($movement['movement_type']) ?></td><td><?= number_format((float)$movement['quantity'], 2, ',', ' ') ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div></div>
    </div>
</div>

<?php if ((int)$user['is_admin'] === 1): ?>
<div class="card shadow-sm mt-4"><div class="card-body">
    <h5>Edition des modèles de rapports (admin)</h5>
    <div class="row g-4">
        <?php foreach ($templates as $template): ?>
            <div class="col-lg-6">
                <form method="post" enctype="multipart/form-data" class="border rounded p-3">
                    <input type="hidden" name="report_key" value="<?= htmlspecialchars($template['report_key']) ?>">
                    <input type="hidden" name="existing_logo" value="<?= htmlspecialchars((string)$template['logo_path']) ?>">
                    <h6><?= htmlspecialchars($template['report_key']) ?></h6>
                    <input class="form-control mb-2" name="report_title" value="<?= htmlspecialchars($template['report_title']) ?>">
                    <input class="form-control mb-2" name="report_subtitle" value="<?= htmlspecialchars((string)$template['report_subtitle']) ?>">
                    <input class="form-control mb-2" type="file" name="logo" accept="image/*">
                    <button class="btn btn-outline-primary btn-sm">Sauvegarder</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div></div>
<?php endif; ?>
<?php renderFooter(); ?>
