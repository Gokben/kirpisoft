<?php
declare(strict_types=1);

session_start();
const ROOT = __DIR__ . '/..';

function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function config_exists(): bool { return is_file(ROOT . '/config.php'); }
function db(): PDO {
    static $pdo;
    if ($pdo instanceof PDO) return $pdo;
    if (!config_exists()) { header('Location: install.php'); exit; }
    $c = require ROOT . '/config.php';
    $pdo = new PDO("mysql:host={$c['db_host']};dbname={$c['db_name']};charset=utf8mb4", $c['db_user'], $c['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
function setting(string $key, string $default = ''): string {
    static $values;
    if ($values === null) {
        $values = [];
        foreach (db()->query('SELECT setting_key, setting_value FROM settings') as $row) $values[$row['setting_key']] = $row['setting_value'];
    }
    return $values[$key] ?? $default;
}
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function verify_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('Oturum doğrulaması başarısız.'); }
}
function admin_required(): void {
    if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
}
