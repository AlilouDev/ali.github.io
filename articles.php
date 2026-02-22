<?php
require_once __DIR__ . '/app/functions.php';
$title = 'Articles';
require __DIR__ . '/partials/header.php';
require_privilege($user, 'articles');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_category'])) {
        db()->prepare('INSERT INTO categories(name) VALUES (?)')->execute([trim($_POST['name'])]);
        flash('ok', 'Catégorie ajoutée');
    } else {
        db()->prepare('INSERT INTO articles(name, sku, category_id, stock, avg_cost, created_at) VALUES (?,?,?,?,?,?)')
            ->execute([
                trim($_POST['name']),
                trim($_POST['sku']),
                ($_POST['category_id'] ?: null),
                0,
                0,
                date('Y-m-d H:i:s')
            ]);
        flash('ok', 'Article ajouté');
    }
    header('Location: /articles.php');
    exit;
}
$cats = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$items = article_options();
?>
<div class="row g-3">
  <div class="col-lg-4"><div class="card"><div class="card-body"><h5>Nouvel article</h5><form method="post">
    <input class="form-control mb-2" name="name" placeholder="Nom" required>
    <input class="form-control mb-2" name="sku" placeholder="SKU">
    <select name="category_id" class="form-select mb-2"><option value="">Catégorie</option><?php foreach($cats as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?></select>
    <button class="btn btn-primary">Ajouter</button></form></div></div>
  <div class="card mt-3"><div class="card-body"><h6>Nouvelle catégorie</h6><form method="post"><input type="hidden" name="new_category" value="1"><input class="form-control mb-2" name="name" placeholder="Catégorie" required><button class="btn btn-outline-primary">Ajouter catégorie</button></form></div></div>
  </div>
  <div class="col-lg-8"><div class="card"><div class="card-body"><h5>Table Articles</h5><div class="table-responsive"><table class="table table-striped"><tr><th>Nom</th><th>SKU</th><th>Catégorie</th><th>Stock</th><th>CUMP</th></tr><?php foreach($items as $a): ?><tr><td><?= htmlspecialchars($a['name']) ?></td><td><?= htmlspecialchars($a['sku'] ?? '') ?></td><td><?= htmlspecialchars($a['category'] ?? '-') ?></td><td><strong><?= number_format((float)$a['stock'],2,',',' ') ?></strong></td><td><?= number_format((float)$a['avg_cost'],2,',',' ') ?> €</td></tr><?php endforeach; ?></table></div></div></div></div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
