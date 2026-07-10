<?php
require __DIR__.'/../inc/bootstrap.php';admin_required();
db()->exec('UPDATE messages SET is_read=1 WHERE is_read=0');$messages=db()->query('SELECT * FROM messages ORDER BY created_at DESC LIMIT 200')->fetchAll();
$pageTitle='Mesajlar';$activeMenu='messages';require __DIR__.'/_header.php';
?><div class="page-head"><div><h1>Mesajlar</h1><p>İletişim formundan gelen talepler</p></div></div><section class="card"><?php if(!$messages):?><p>Henüz mesaj yok.</p><?php endif?><div class="activity"><?php foreach($messages as $m):?><div><span class="activity-icon">✉</span><p><strong><?=e($m['name'])?> · <?=e($m['subject'])?></strong><small><a href="mailto:<?=e($m['email'])?>"><?=e($m['email'])?></a><br><?=nl2br(e($m['message']))?></small></p><time><?=e($m['created_at'])?></time></div><?php endforeach?></div></section><?php require __DIR__.'/_footer.php';?>
