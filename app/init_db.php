<?php
require_once __DIR__ . '/config.php';

$pdo = db();
$pdo->exec('CREATE TABLE IF NOT EXISTS roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL,
    privileges TEXT NOT NULL DEFAULT "[]"
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role_id INTEGER NOT NULL,
    active INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY(role_id) REFERENCES roles(id)
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    sku TEXT UNIQUE,
    category_id INTEGER,
    stock REAL NOT NULL DEFAULT 0,
    avg_cost REAL NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL,
    FOREIGN KEY(category_id) REFERENCES categories(id)
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS movements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    article_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    type TEXT NOT NULL CHECK(type IN ("initial","in","out","adjustment")),
    quantity REAL NOT NULL,
    unit_cost REAL,
    note TEXT,
    movement_date TEXT NOT NULL,
    created_at TEXT NOT NULL,
    FOREIGN KEY(article_id) REFERENCES articles(id),
    FOREIGN KEY(user_id) REFERENCES users(id)
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS report_settings (
    id INTEGER PRIMARY KEY CHECK (id = 1),
    company_name TEXT NOT NULL DEFAULT "Ma Société",
    stock_report_title TEXT NOT NULL DEFAULT "État du stock au",
    card_report_title TEXT NOT NULL DEFAULT "Fiche de stock au",
    logo_url TEXT NOT NULL DEFAULT ""
)');

$pdo->exec('INSERT OR IGNORE INTO report_settings(id) VALUES (1)');

$adminPrivileges = json_encode([
    'dashboard', 'articles', 'movements', 'history', 'users', 'roles', 'reports', 'settings'
]);
$managerPrivileges = json_encode(['dashboard', 'articles', 'movements', 'history', 'reports']);

$stmt = $pdo->prepare('INSERT OR IGNORE INTO roles(name, privileges) VALUES (?, ?)');
$stmt->execute(['admin', $adminPrivileges]);
$stmt->execute(['manager', $managerPrivileges]);

$roleId = (int) $pdo->query("SELECT id FROM roles WHERE name = 'admin'")->fetchColumn();
$hash = password_hash('admin', PASSWORD_DEFAULT);
$uStmt = $pdo->prepare('INSERT OR IGNORE INTO users(username, password, role_id, active) VALUES (?, ?, ?, 1)');
$uStmt->execute(['admin', $hash, $roleId]);
