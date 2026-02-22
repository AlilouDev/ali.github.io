<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function currentStockForArticle(int $articleId): float
{
    $stmt = db()->prepare("SELECT COALESCE(SUM(CASE
        WHEN movement_type IN ('ENTREE', 'INITIAL', 'CORRECTION_PLUS') THEN quantity
        WHEN movement_type IN ('SORTIE', 'CORRECTION_MINUS') THEN -quantity
        ELSE 0 END), 0)
        FROM movements WHERE article_id = :article_id");
    $stmt->execute([':article_id' => $articleId]);

    return (float)$stmt->fetchColumn();
}
