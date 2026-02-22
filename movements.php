<?php
require_once __DIR__ . '/app/functions.php';
$title = 'Mouvements';
require __DIR__ . '/partials/header.php';
require_privilege($user, 'movements');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        apply_movement(
            (int)$_POST['article_id'],
            (int)$user['id'],
            $_POST['type'],
            (float)$_POST['quantity'],
            ($_POST['unit_cost'] === '' ? null : (float)$_POST['unit_cost']),
            trim($_POST['note'] ?? ''),
            $_POST['movement_date'] ?: date('Y-m-d')
        );
        flash('ok', 'Mouvement enregistré');
    } catch (Throwable $e) {
        flash('err', $e->getMessage());
    }
    header('Location: /movements.php');
    exit;
}
$articles = article_options();
?>
<div class="card"><div class="card-body">
  <h5>Saisie mouvement de stock</h5>
  <form method="post" class="row g-3" id="mv-form">
    <div class="col-md-4"><label class="form-label">Article</label><select name="article_id" id="article" class="form-select" required><option value="">Choisir</option><?php foreach($articles as $a): ?><option value="<?= $a['id'] ?>" data-stock="<?= $a['stock'] ?>" data-cost="<?= $a['avg_cost'] ?>"><?= htmlspecialchars($a['name']) ?></option><?php endforeach; ?></select><small class="text-muted">Stock actuel: <span id="stock-current">0</span> | CUMP: <span id="cost-current">0</span> €</small></div>
    <div class="col-md-2"><label class="form-label">Type</label><select name="type" class="form-select" required><option value="initial">Stock initial</option><option value="in">Entrée</option><option value="out">Sortie</option><option value="adjustment">Correction</option></select></div>
    <div class="col-md-2"><label class="form-label">Quantité</label><input type="number" step="0.01" name="quantity" class="form-control" required></div>
    <div class="col-md-2"><label class="form-label">Coût unitaire</label><input type="number" step="0.01" name="unit_cost" class="form-control"></div>
    <div class="col-md-2"><label class="form-label">Date</label><input type="date" name="movement_date" value="<?= date('Y-m-d') ?>" class="form-control" required></div>
    <div class="col-12"><label class="form-label">Note</label><input name="note" class="form-control" placeholder="Commentaire"></div>
    <div class="col-12"><button class="btn btn-primary">Valider mouvement</button></div>
  </form>
</div></div>
<script>
const article = document.getElementById('article');
article?.addEventListener('change', () => {
  const op = article.options[article.selectedIndex];
  document.getElementById('stock-current').textContent = Number(op?.dataset.stock || 0).toFixed(2);
  document.getElementById('cost-current').textContent = Number(op?.dataset.cost || 0).toFixed(2);
});
</script>
<?php require __DIR__ . '/partials/footer.php'; ?>
