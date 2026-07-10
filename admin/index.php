<?php
require __DIR__.'/../inc/bootstrap.php'; admin_required();
ensure_visits_table(); ensure_pages_table();
$stats=[
 'online'=>(int)db()->query("SELECT COUNT(DISTINCT visitor_id) FROM visits WHERE visited_at >= (NOW() - INTERVAL 5 MINUTE)")->fetchColumn(),
 'today'=>(int)db()->query('SELECT COUNT(*) FROM visits WHERE visited_at >= CURDATE()')->fetchColumn(),
 'messages'=>(int)db()->query('SELECT COUNT(*) FROM messages WHERE is_read=0')->fetchColumn(),
];
$recent=db()->query('SELECT ip_address,country,page,visited_at FROM visits ORDER BY id DESC LIMIT 5')->fetchAll();
$pageTitle='Ana Sayfa'; $activeMenu='dashboard'; require __DIR__.'/_header.php';
?>
<div class="page-head"><div><h1>Hoş geldin, Yönetici</h1><p>Sitenin bugünkü özeti</p></div><a class="btn secondary" href="../index.php" target="_blank">Siteyi Aç</a></div>
<section class="stats">
 <article class="card stat online"><span>Şu anda içeride</span><strong><?=number_format($stats['online'],0,',','.')?></strong><small>Son 5 dakika</small></article>
 <article class="card stat"><span>Bugünkü ziyaret</span><strong><?=number_format($stats['today'],0,',','.')?></strong><small>Sayfa görüntülenmesi</small></article>
 <article class="card stat"><span>Yeni mesaj</span><strong><?=number_format($stats['messages'],0,',','.')?></strong><small>Okunmamış mesaj</small></article>
</section>
<div class="dashboard-grid"><section class="card"><div class="section-head"><h2>Son hareketler</h2><a href="reports.php">Tümünü gör</a></div><?php if(!$recent):?><p>Henüz hareket yok.</p><?php endif?><div class="activity"><?php foreach($recent as $row):?><div><span class="activity-icon">●</span><p><strong><?=e($row['ip_address'])?></strong><small><?=e($row['country'].' · '.$row['page'])?></small></p><time><?=e($row['visited_at'])?></time></div><?php endforeach?></div></section><aside class="card"><h2>Hızlı işlemler</h2><div class="quick"><a class="btn" href="settings.php">Siteyi Düzenle</a><a class="btn secondary" href="messages.php">Mesajları Aç</a><a class="btn secondary" href="reports.php">Raporlara Git</a><a class="btn secondary" href="page-edit.php">Yeni Sayfa Ekle</a></div></aside></div>
<?php require __DIR__.'/_footer.php'; ?>
