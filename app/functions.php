<?php
require_once __DIR__ . '/config.php';

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }
    $v = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $v;
}

function article_options(): array
{
    $sql = 'SELECT a.id, a.name, a.sku, a.stock, a.avg_cost, c.name as category FROM articles a LEFT JOIN categories c ON c.id = a.category_id ORDER BY a.name';
    return db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function apply_movement(int $articleId, int $userId, string $type, float $quantity, ?float $unitCost, string $note, string $date): void
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT * FROM articles WHERE id = ?');
        $stmt->execute([$articleId]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$article) {
            throw new RuntimeException('Article introuvable');
        }

        $stock = (float)$article['stock'];
        $avgCost = (float)$article['avg_cost'];
        $cost = $unitCost ?? $avgCost;

        if ($type === 'out') {
            if ($quantity > $stock) {
                throw new RuntimeException('Stock insuffisant pour cette sortie');
            }
            $newStock = $stock - $quantity;
            $newAvg = $avgCost;
        } elseif ($type === 'in' || $type === 'initial') {
            $newStock = $stock + $quantity;
            $incomingCost = $unitCost ?? 0;
            $newAvg = $newStock > 0 ? (($stock * $avgCost) + ($quantity * $incomingCost)) / $newStock : 0;
            $cost = $incomingCost;
        } else {
            $newStock = $stock + $quantity;
            if ($newStock < 0) {
                throw new RuntimeException('Correction invalide : stock négatif');
            }
            if ($quantity > 0 && $unitCost !== null) {
                $newAvg = $newStock > 0 ? (($stock * $avgCost) + ($quantity * $unitCost)) / $newStock : 0;
                $cost = $unitCost;
            } else {
                $newAvg = $avgCost;
                $cost = $avgCost;
            }
        }

        $up = $pdo->prepare('UPDATE articles SET stock = ?, avg_cost = ? WHERE id = ?');
        $up->execute([$newStock, $newAvg, $articleId]);

        $ins = $pdo->prepare('INSERT INTO movements(article_id, user_id, type, quantity, unit_cost, note, movement_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $ins->execute([$articleId, $userId, $type, $quantity, $cost, $note, $date, date('Y-m-d H:i:s')]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
