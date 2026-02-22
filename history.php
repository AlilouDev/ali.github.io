<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/stock.php';

$user = requireLogin();
$pdo = db();

$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');
$type = $_GET['type'] ?? '';

$sql = 'SELECT m.*, a.name AS article_name, u.username
        FROM movements m
        JOIN articles a ON a.id = m.article_id
        LEFT JOIN users u ON u.id = m.created_by
        WHERE DATE(m.movement_date) BETWEEN :from AND :to';
$params = [':from' => $from, ':to' => $to];
if ($type !== '') {
    $sql .= ' AND m.movement_type = :type';
    $params[':type'] = $type;
}
$sql .= ' ORDER BY m.movement_date DESC, m.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

renderHeader('Historique des mouvements', $user);
?>
<div class="card shadow-sm mb-3"><div class="card-body">
<form class="row g-2">
    <div class="col-md-3"><input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="form-control"></div>
    <div class="col-md-3"><input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="form-control"></div>
    <div class="col-md-3">
        <select name="type" class="form-select">
            <option value="">Tous les types</option>
            <?php foreach (['INITIAL', 'ENTREE', 'SORTIE', 'CORRECTION_PLUS', 'CORRECTION_MINUS'] as $movementType): ?>
                <option value="<?= $movementType ?>" <?= $type === $movementType ? 'selected' : '' ?>><?= $movementType ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3"><button class="btn btn-primary w-100">Rechercher</button></div>
</form>
</div></div>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead><tr><th>Date</th><th>Article</th><th>Type</th><th>Qté</th><th>Note</th><th>Par</th></tr></thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['movement_date']) ?></td>
                        <td><?= htmlspecialchars($row['article_name']) ?></td>
                        <td><?= htmlspecialchars($row['movement_type']) ?></td>
                        <td><?= number_format((float)$row['quantity'], 2, ',', ' ') ?></td>
                        <td><?= htmlspecialchars((string)$row['note']) ?></td>
                        <td><?= htmlspecialchars((string)($row['username'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php renderFooter(); ?>
