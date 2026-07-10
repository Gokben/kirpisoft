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
function ensure_visits_table(): void {
    db()->exec("CREATE TABLE IF NOT EXISTS visits (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, visitor_id VARCHAR(64) NOT NULL, ip_address VARCHAR(45) NOT NULL, country VARCHAR(100) NOT NULL DEFAULT 'Bilinmiyor', page VARCHAR(255) NOT NULL, user_agent VARCHAR(500) NOT NULL DEFAULT '', visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_visited_at (visited_at), INDEX idx_ip_address (ip_address), INDEX idx_country (country)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
function country_name(string $code): string {
    $code = strtoupper($code);
    $names = ['TR'=>'Türkiye','DE'=>'Almanya','US'=>'Amerika Birleşik Devletleri','GB'=>'Birleşik Krallık','FR'=>'Fransa','NL'=>'Hollanda','BE'=>'Belçika','AT'=>'Avusturya','CH'=>'İsviçre','AZ'=>'Azerbaycan','CY'=>'Kıbrıs','RU'=>'Rusya','UA'=>'Ukrayna','BG'=>'Bulgaristan','GR'=>'Yunanistan','IT'=>'İtalya','ES'=>'İspanya','CA'=>'Kanada','AU'=>'Avustralya'];
    return $names[$code] ?? ($code ?: 'Bilinmiyor');
}
function lookup_country(string $ip): string {
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return 'Bilinmiyor';
    $url = 'https://api.country.is/'.rawurlencode($ip);
    $json = false;
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_CONNECTTIMEOUT=>2, CURLOPT_TIMEOUT=>3, CURLOPT_USERAGENT=>'Kirpisoft-CMS/1.0']);
        $json = curl_exec($curl);
        curl_close($curl);
    } elseif (filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
        $context = stream_context_create(['http'=>['timeout'=>3,'header'=>"User-Agent: Kirpisoft-CMS/1.0\r\n"]]);
        $json = @file_get_contents($url, false, $context);
    }
    $data = is_string($json) ? json_decode($json, true) : null;
    return country_name(is_array($data) ? (string)($data['country'] ?? '') : '');
}
function resolve_country(string $ip): string {
    $stmt = db()->prepare("SELECT country FROM visits WHERE ip_address=? AND country<>'Bilinmiyor' ORDER BY id DESC LIMIT 1");
    $stmt->execute([$ip]);
    $known = $stmt->fetchColumn();
    return is_string($known) && $known !== '' ? $known : lookup_country($ip);
}
function backfill_visit_countries(): void {
    $ips = db()->query("SELECT DISTINCT ip_address FROM visits WHERE country='Bilinmiyor' LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
    $update = db()->prepare("UPDATE visits SET country=? WHERE ip_address=? AND country='Bilinmiyor'");
    foreach ($ips as $ip) {
        $country = lookup_country((string)$ip);
        if ($country !== 'Bilinmiyor') $update->execute([$country, $ip]);
    }
}
function track_visit(string $page): void {
    try {
        ensure_visits_table();
        $visitorId = $_SESSION['visitor_id'] ??= bin2hex(random_bytes(16));
        $ip = substr((string)($_SERVER['REMOTE_ADDR'] ?? 'Bilinmiyor'), 0, 45);
        $country = (string)($_SERVER['HTTP_GEOIP_COUNTRY_NAME'] ?? $_SERVER['HTTP_CF_IPCOUNTRY'] ?? $_SERVER['HTTP_X_COUNTRY_CODE'] ?? '');
        $country = $country !== '' ? country_name($country) : resolve_country($ip);
        $agent = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);
        $stmt = db()->prepare('INSERT INTO visits(visitor_id,ip_address,country,page,user_agent) VALUES(?,?,?,?,?)');
        $stmt->execute([$visitorId, $ip, substr($country, 0, 100), substr($page, 0, 255), $agent]);
    } catch (Throwable $e) { error_log('Visit tracking failed: '.$e->getMessage()); }
}
