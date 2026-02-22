<?php

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $databasePath = __DIR__ . '/../stock.sqlite';
    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    initializeDatabase($pdo);

    return $pdo;
}

function initializeDatabase(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS roles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS privileges (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS role_privileges (
        role_id INTEGER NOT NULL,
        privilege_id INTEGER NOT NULL,
        PRIMARY KEY(role_id, privilege_id),
        FOREIGN KEY(role_id) REFERENCES roles(id),
        FOREIGN KEY(privilege_id) REFERENCES privileges(id)
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        role_id INTEGER,
        is_admin INTEGER DEFAULT 0,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(role_id) REFERENCES roles(id)
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS articles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        category_id INTEGER,
        sku TEXT,
        unit TEXT,
        FOREIGN KEY(category_id) REFERENCES categories(id)
    )');


    $pdo->exec('CREATE TABLE IF NOT EXISTS report_templates (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        report_key TEXT UNIQUE NOT NULL,
        report_title TEXT NOT NULL,
        report_subtitle TEXT,
        logo_path TEXT
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS movements (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        article_id INTEGER NOT NULL,
        movement_type TEXT NOT NULL,
        quantity REAL NOT NULL,
        movement_date TEXT NOT NULL,
        note TEXT,
        created_by INTEGER,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(article_id) REFERENCES articles(id),
        FOREIGN KEY(created_by) REFERENCES users(id)
    )');

    $defaultRoles = ['Admin', 'Magasinier', 'Lecteur'];
    $stmtRole = $pdo->prepare('INSERT OR IGNORE INTO roles(name) VALUES(:name)');
    foreach ($defaultRoles as $role) {
        $stmtRole->execute([':name' => $role]);
    }

    $defaultPrivileges = [
        'view_dashboard',
        'manage_articles',
        'manage_movements',
        'view_reports',
        'manage_users',
        'manage_settings',
    ];

    $stmtPrivilege = $pdo->prepare('INSERT OR IGNORE INTO privileges(name) VALUES(:name)');
    foreach ($defaultPrivileges as $privilege) {
        $stmtPrivilege->execute([':name' => $privilege]);
    }

    $adminRoleId = (int)$pdo->query("SELECT id FROM roles WHERE name = 'Admin'")->fetchColumn();
    $allPrivilegeIds = $pdo->query('SELECT id FROM privileges')->fetchAll(PDO::FETCH_COLUMN);
    $stmtRolePrivilege = $pdo->prepare('INSERT OR IGNORE INTO role_privileges(role_id, privilege_id) VALUES(:role_id, :privilege_id)');
    foreach ($allPrivilegeIds as $privilegeId) {
        $stmtRolePrivilege->execute([':role_id' => $adminRoleId, ':privilege_id' => (int)$privilegeId]);
    }


    $stmtTemplate = $pdo->prepare('INSERT OR IGNORE INTO report_templates(report_key, report_title, report_subtitle, logo_path) VALUES(:report_key,:report_title,:report_subtitle,:logo_path)');
    $stmtTemplate->execute([':report_key' => 'etat_stock', ':report_title' => 'Etat du stock au ...', ':report_subtitle' => 'Rapport synthétique des quantités', ':logo_path' => '']);
    $stmtTemplate->execute([':report_key' => 'fiche_stock', ':report_title' => 'Fiche de stock au ...', ':report_subtitle' => 'Rapport détaillé des mouvements', ':logo_path' => '']);

    $exists = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE username = 'admin'")->fetchColumn();
    if ($exists === 0) {
        $insert = $pdo->prepare('INSERT INTO users(username, password_hash, role_id, is_admin) VALUES(:username, :password_hash, :role_id, 1)');
        $insert->execute([
            ':username' => 'admin',
            ':password_hash' => password_hash('admin', PASSWORD_DEFAULT),
            ':role_id' => $adminRoleId,
        ]);
    }
}
