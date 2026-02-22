<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/stock.php';

$user = requireLogin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $articleId = (int)($_POST['article_id'] ?? 0);
    $type = (string)($_POST['movement_type'] ?? '');
    $quantity = (float)($_POST['quantity'] ?? 0);
    $movementDate = (string)($_POST['movement_date'] ?? date('Y-m-d'));
    $note = trim($_POST['note'] ?? '');

    if ($articleId > 0 && $quantity > 0) {
        $stmt = $pdo->prepare('INSERT INTO movements(article_id, movement_type, quantity, movement_date, note, created_by) VALUES(:article_id, :movement_type, :quantity, :movement_date, :note, :created_by)');
        $stmt->execute([
            ':article_id' => $articleId,
            ':movement_type' => $type,
            ':quantity' => $quantity,
            ':movement_date' => $movementDate,
            ':note' => $note,
            ':created_by' => (int)$user['id'],
        ]);
    }

    header('Location: movements.php?article_id=' . $articleId);
    exit;
}

$articles = $pdo->query('SELECT id, name FROM articles ORDER BY name')->fetchAll();
$selectedArticleId = (int)($_GET['article_id'] ?? ($articles[0]['id'] ?? 0));
$selectedStock = $selectedArticleId ? currentStockForArticle($selectedArticleId) : 0;

renderHeader('Saisie des mouvements', $user);
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm"><div class="card-body">
            <h5>Nouveau mouvement</h5>
            <div class="alert alert-info">Stock en cours: <strong><?= number_format($selectedStock, 2, ',', ' ') ?></strong></div>
            <form method="post" class="d-grid gap-2">
                <select class="form-select" name="article_id" onchange="window.location='movements.php?article_id='+this.value" required>
                    <?php foreach ($articles as $article): ?>
                        <option value="<?= (int)$article['id'] ?>" <?= $selectedArticleId === (int)$article['id'] ? 'selected' : '' ?>><?= htmlspecialchars($article['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="form-select" name="movement_type" required>
                    <option value="INITIAL">Stock initial</option>
                    <option value="ENTREE">Entrée</option>
                    <option value="SORTIE">Sortie</option>
                    <option value="CORRECTION_PLUS">Correction +</option>
                    <option value="CORRECTION_MINUS">Correction -</option>
                </select>
                <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" placeholder="Quantité" required>
                <input type="date" class="form-control" name="movement_date" value="<?= date('Y-m-d') ?>" required>
                <textarea name="note" class="form-control" placeholder="Commentaire"></textarea>
                <button class="btn btn-primary">Valider le mouvement</button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-sm"><div class="card-header bg-white">Guide des types</div>
            <div class="card-body">
                <ul>
                    <li><strong>Stock initial:</strong> point de départ du stock.</li>
                    <li><strong>Entrée/Sortie:</strong> flux opérationnels.</li>
                    <li><strong>Correction +/-:</strong> ajustement inventaire.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php renderFooter(); ?>
