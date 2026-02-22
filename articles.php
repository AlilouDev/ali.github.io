<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/stock.php';

$user = requireLogin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['category_name'] ?? '');
        if ($name !== '') {
            $stmt = $pdo->prepare('INSERT OR IGNORE INTO categories(name) VALUES(:name)');
            $stmt->execute([':name' => $name]);
        }
    }

    if (isset($_POST['add_article'])) {
        $stmt = $pdo->prepare('INSERT INTO articles(name, category_id, sku, unit) VALUES(:name, :category_id, :sku, :unit)');
        $stmt->execute([
            ':name' => trim($_POST['name'] ?? ''),
            ':category_id' => (int)($_POST['category_id'] ?: 0) ?: null,
            ':sku' => trim($_POST['sku'] ?? ''),
            ':unit' => trim($_POST['unit'] ?? ''),
        ]);
    }
    header('Location: articles.php');
    exit;
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$articles = $pdo->query('SELECT a.*, c.name AS category_name FROM articles a LEFT JOIN categories c ON c.id = a.category_id ORDER BY a.name')->fetchAll();

renderHeader('Articles et Catégories', $user);
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4"><div class="card-body">
            <h5>Nouvelle catégorie</h5>
            <form method="post" class="d-grid gap-2">
                <input class="form-control" name="category_name" placeholder="Nom catégorie" required>
                <button class="btn btn-outline-primary" name="add_category">Ajouter</button>
            </form>
        </div></div>
        <div class="card shadow-sm"><div class="card-body">
            <h5>Nouvel article</h5>
            <form method="post" class="d-grid gap-2">
                <input class="form-control" name="name" placeholder="Nom article" required>
                <input class="form-control" name="sku" placeholder="SKU">
                <input class="form-control" name="unit" placeholder="Unité (pcs, kg...)" value="pcs">
                <select class="form-select" name="category_id">
                    <option value="">Catégorie</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int)$category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" name="add_article">Enregistrer</button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><strong>Table articles</strong></div>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Article</th><th>Catégorie</th><th>SKU</th><th>Unité</th><th>Stock en cours</th></tr></thead>
                    <tbody>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td><?= htmlspecialchars($article['name']) ?></td>
                            <td><?= htmlspecialchars((string)($article['category_name'] ?? '-')) ?></td>
                            <td><?= htmlspecialchars((string)$article['sku']) ?></td>
                            <td><?= htmlspecialchars((string)$article['unit']) ?></td>
                            <td><strong><?= number_format(currentStockForArticle((int)$article['id']), 2, ',', ' ') ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php renderFooter(); ?>
