<?php
require_once __DIR__ . '/app/functions.php';
$title = 'Dashboard';
require __DIR__ . '/partials/header.php';
require_privilege($user, 'dashboard');

$totals = db()->query('SELECT COUNT(*) as articles, COALESCE(SUM(stock),0) as qty, COALESCE(SUM(stock * avg_cost),0) as value FROM articles')->fetch(PDO::FETCH_ASSOC);
$lastIn = db()->query("SELECT m.*, a.name as article FROM movements m JOIN articles a ON a.id = m.article_id WHERE m.type IN ('initial','in') ORDER BY m.id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$lastOut = db()->query("SELECT m.*, a.name as article FROM movements m JOIN articles a ON a.id = m.article_id WHERE m.type='out' ORDER BY m.id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="card"><div class="card-body"><small>Articles</small><h3><?= (int)$totals['articles'] ?></h3></div></div></div>
  <div class="col-md-4"><div class="card"><div class="card-body"><small>Stock total</small><h3><?= number_format((float)$totals['qty'],2,',',' ') ?></h3></div></div></div>
  <div class="col-md-4"><div class="card"><div class="card-body"><small>Valeur stock (CUMP)</small><h3><?= number_format((float)$totals['value'],2,',',' ') ?> €</h3></div></div></div>
</div>
<div class="row g-3">
  <div class="col-lg-6"><div class="card"><div class="card-body"><h5>Dernières entrées</h5><ul class="list-group list-group-flush"><?php foreach($lastIn as $m): ?><li class="list-group-item d-flex justify-content-between"><span><?= htmlspecialchars($m['article']) ?> (<?= htmlspecialchars($m['type']) ?>)</span><strong>+<?= $m['quantity'] ?></strong></li><?php endforeach; ?></ul></div></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-body"><h5>Dernières sorties</h5><ul class="list-group list-group-flush"><?php foreach($lastOut as $m): ?><li class="list-group-item d-flex justify-content-between"><span><?= htmlspecialchars($m['article']) ?></span><strong>-<?= $m['quantity'] ?></strong></li><?php endforeach; ?></ul></div></div></div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
