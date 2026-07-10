<?php
require __DIR__ . '/inc/bootstrap.php';
$html = file_get_contents(__DIR__ . '/index.html');
$replacements = [
    'Kirpisoft - Web ve Mobil Mini Uygulamalar Platformu' => setting('site_title','Kirpisoft - Web ve Mobil Mini Uygulamalar Platformu'),
    'Kirpisoft Platform' => setting('brand','Kirpisoft Platform'),
    "Küçük İş Uygulamalarını\n                                                <span>Tek Platformda Yönet" => e(setting('hero_title','Küçük İş Uygulamalarını Tek Platformda Yönet')) . "\n                                                <span>",
    'iletisim.html' => 'iletisim.php',
    'index.html' => 'index.php',
];
echo str_replace(array_keys($replacements), array_values($replacements), $html);
