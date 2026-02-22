<?php
require_once __DIR__ . '/app/functions.php';
$title = 'États';
require __DIR__ . '/partials/header.php';
require_privilege($user, 'reports');

$settings = db()->query('SELECT * FROM report_settings WHERE id=1')->fetch(PDO::FETCH_ASSOC);
$asOf = $_GET['as_of'] ?? date('Y-m-d');
$stockRows = db()->query('SELECT name, stock, avg_cost, (stock*avg_cost) total FROM articles ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$articleId = (int)($_GET['article_id'] ?? 0);
$articleList = article_options();
$cardRows = [];
if ($articleId) {
    $st = db()->prepare('SELECT m.*, u.username FROM movements m JOIN users u ON u.id=m.user_id WHERE article_id=? ORDER BY movement_date,id');
    $st->execute([$articleId]);
    $cardRows = $st->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="card mb-3"><div class="card-body">
<form class="row g-2"><div class="col-md-3"><input type="date" name="as_of" value="<?= htmlspecialchars($asOf) ?>" class="form-control"></div><div class="col-md-5"><select class="form-select" name="article_id"><option value="0">Fiche stock: choisir article</option><?php foreach($articleList as $a): ?><option value="<?= $a['id'] ?>" <?= $articleId===$a['id']?'selected':'' ?>><?= htmlspecialchars($a['name']) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><button class="btn btn-primary w-100">Générer</button></div></form>
</div></div>
<div class="card mb-3"><div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-2"><div><h5 class="mb-0"><?= htmlspecialchars($settings['stock_report_title']) ?> <?= htmlspecialchars($asOf) ?></h5><small><?= htmlspecialchars($settings['company_name']) ?></small></div><?php if($settings['logo_url']): ?><img src="<?= htmlspecialchars($settings['logo_url']) ?>" alt="logo" style="height:50px"><?php endif; ?></div>
<table class="table table-bordered"><tr><th>Article</th><th>Stock</th><th>CUMP</th><th>Valeur</th></tr><?php foreach($stockRows as $r): ?><tr><td><?= htmlspecialchars($r['name']) ?></td><td><?= number_format((float)$r['stock'],2,',',' ') ?></td><td><?= number_format((float)$r['avg_cost'],2,',',' ') ?></td><td><?= number_format((float)$r['total'],2,',',' ') ?></td></tr><?php endforeach; ?></table>
</div></div>
<?php if($articleId): ?><div class="card"><div class="card-body"><div class="d-flex justify-content-between align-items-center mb-2"><h5><?= htmlspecialchars($settings['card_report_title']) ?> <?= htmlspecialchars($asOf) ?></h5><?php if($settings['logo_url']): ?><img src="<?= htmlspecialchars($settings['logo_url']) ?>" alt="logo" style="height:50px"><?php endif; ?></div>
<table class="table table-sm"><tr><th>Date</th><th>Type</th><th>Qté</th><th>Cout</th><th>Utilisateur</th><th>Note</th></tr><?php foreach($cardRows as $r): ?><tr><td><?= htmlspecialchars($r['movement_date']) ?></td><td><?= htmlspecialchars($r['type']) ?></td><td><?= number_format((float)$r['quantity'],2,',',' ') ?></td><td><?= number_format((float)$r['unit_cost'],2,',',' ') ?></td><td><?= htmlspecialchars($r['username']) ?></td><td><?= htmlspecialchars($r['note']) ?></td></tr><?php endforeach; ?></table>
</div></div><?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
