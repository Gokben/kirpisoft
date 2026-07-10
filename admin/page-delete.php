<?php
require __DIR__.'/../inc/bootstrap.php';
admin_required();
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Geçersiz istek.');}
verify_csrf();
ensure_pages_table();
$id=(int)($_POST['id']??0);
if($id>0){$stmt=db()->prepare('DELETE FROM pages WHERE id=?');$stmt->execute([$id]);}
header('Location: pages.php');
exit;
