<?php
require __DIR__ . '/inc/bootstrap.php';
if (config_exists()) { header('Location: admin/login.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $host = trim($_POST['host'] ?? 'localhost'); $name = trim($_POST['name'] ?? '');
    $user = trim($_POST['user'] ?? ''); $pass = $_POST['pass'] ?? '';
    $admin = trim($_POST['admin'] ?? 'admin'); $adminPass = $_POST['admin_pass'] ?? '';
    try {
        if (!$name || !$user || strlen($adminPass) < 8) throw new RuntimeException('Bilgileri kontrol edin; yönetici parolası en az 8 karakter olmalı.');
        $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $sql = file_get_contents(__DIR__ . '/database.sql');
        $pdo->exec($sql);
        $stmt = $pdo->prepare('INSERT INTO admins (username,password_hash) VALUES (?,?)');
        $stmt->execute([$admin, password_hash($adminPass, PASSWORD_DEFAULT)]);
        $cfg = "<?php\nreturn " . var_export(['db_host'=>$host,'db_name'=>$name,'db_user'=>$user,'db_pass'=>$pass], true) . ";\n";
        if (file_put_contents(__DIR__ . '/config.php', $cfg, LOCK_EX) === false) throw new RuntimeException('config.php yazılamadı.');
        header('Location: admin/login.php?installed=1'); exit;
    } catch (Throwable $e) { $error = $e->getMessage(); }
}
?>
<!doctype html><html lang="tr"><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>Kirpisoft CMS Kurulum</title><link rel="stylesheet" href="admin/admin.css"><body><main class="auth"><form method="post" class="card"><h1>Kirpisoft CMS Kurulum</h1><?php if($error):?><p class="error"><?=e($error)?></p><?php endif?><input type="hidden" name="csrf" value="<?=csrf()?>"><label>MySQL sunucu<input name="host" value="localhost" required></label><label>Veritabanı adı<input name="name" required></label><label>Veritabanı kullanıcı<input name="user" required></label><label>Veritabanı parola<input type="password" name="pass"></label><hr><label>Yönetici kullanıcı adı<input name="admin" value="admin" required></label><label>Yönetici parola<input type="password" name="admin_pass" minlength="8" required></label><button>Kurulumu tamamla</button></form></main></body></html>
