# Application de gestion de stock (PHP + Bootstrap)

## Fonctionnalités
- Login (`admin/admin` par défaut)
- Dashboard: état des stocks, dernières entrées/sorties
- Articles/Catégories
- Mouvements séparés (Stock initial, Entrée, Sortie, Correction + / -)
- Historique avec filtres par date et type
- Configuration: utilisateurs, rôles, privilèges
- Rapports simples avec logo + titre éditable en mode admin

## Lancer en local
```bash
php -S 0.0.0.0:8000
```
Puis ouvrir `http://localhost:8000`.

> La base SQLite est initialisée automatiquement dans `stock.sqlite`.
