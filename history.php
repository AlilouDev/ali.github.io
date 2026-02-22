<?php
require_once __DIR__ . '/app/functions.php';
$title = 'Historique';
require __DIR__ . '/partials/header.php';
require_privilege($user, 'history');

$where = [];
$params = [];
if (!empty($_GET['type'])) { $where[] = 'm.type = ?'; $params[] = $_GET['type']; }
if (!empty($_GET['date_from'])) { $where[] = 'date(m.movement_date) >= date(?)'; $params[] = $_GET['date_from']; }
if (!empty($_GET['date_to'])) { $where[] = 'date(m.movement_date) <= date(?)'; $params[] = $_GET['date_to']; }
$sql = 'SELECT m.*, a.name as article, u.username FROM movements m JOIN articles a ON a.id = m.article_id JOIN users u ON u.id = m.user_id';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY m.movement_date DESC, m.id DESC LIMIT 300';
$stmt = db()->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="card mb-3"><div class="card-body">
  <form class="row g-2">
    <div class="col-md-3"><select name="type" class="form-select"><option value="">Tous types</option><option value="initial">Initial</option><option value="in">Entrée</option><option value="out">Sortie</option><option value="adjustment">Correction</option></select></div>
    <div class="col-md-3"><input type="date" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>" class="form-control"></div>
    <div class="col-md-3"><input type="date" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>" class="form-control"></div>
    <div class="col-md-3"><button class="btn btn-primary w-100">Rechercher</button></div>
  </form>
</div></div>
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table table-sm"><tr><th>Date</th><th>Article</th><th>Type</th><th>Qté</th><th>Cout</th><th>Utilisateur</th><th>Note</th></tr><?php foreach($rows as $r): ?><tr><td><?= htmlspecialchars($r['movement_date']) ?></td><td><?= htmlspecialchars($r['article']) ?></td><td><?= htmlspecialchars($r['type']) ?></td><td><?= number_format((float)$r['quantity'],2,',',' ') ?></td><td><?= number_format((float)$r['unit_cost'],2,',',' ') ?></td><td><?= htmlspecialchars($r['username']) ?></td><td><?= htmlspecialchars($r['note'] ?? '') ?></td></tr><?php endforeach; ?></table></div></div></div>
<?php require __DIR__ . '/partials/footer.php'; ?>
