<?php
require __DIR__ . '/inc/bootstrap.php';
track_visit('İletişim');
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $subject=trim($_POST['subject']??''); $message=trim($_POST['message']??'');
    if ($name && filter_var($email,FILTER_VALIDATE_EMAIL) && $message) {
        $s=db()->prepare('INSERT INTO messages(name,email,subject,message) VALUES(?,?,?,?)'); $s->execute([$name,$email,$subject,$message]); $notice='<div class="alert alert-success">Mesajınız alındı. Teşekkür ederiz.</div>';
    } else $notice='<div class="alert alert-danger">Lütfen zorunlu alanları doğru doldurun.</div>';
}
$html=file_get_contents(__DIR__.'/inc/templates/iletisim.template.php');
$form='<form class="kirpisoft-contact-form" method="post"><input type="hidden" name="csrf" value="'.csrf().'">';
$html=str_replace('<form class="kirpisoft-contact-form" action="mailto:bilgi@kirpii.com" method="post" enctype="text/plain">',$notice.$form,$html);
$html=str_replace(['name="Ad"','name="Email"','name="Konu"','name="Mesaj"'],['name="name" required','name="email" required','name="subject"','name="message" required'],$html);
$map=['İletişim Bilgileri'=>setting('contact_title','İletişim Bilgileri'),'+90 (543) 548 01 22'=>setting('phone'),'+905435480122'=>setting('phone_link'),'bilgi@kirpii.com'=>setting('email')];
$seoTitle='İletişim | Kirpisoft Web ve Mobil Uygulamalar';
$seoDescription='Kirpisoft ile web sitesi, mobil uygulama ve özel yazılım projeleriniz için iletişime geçin. Antalya iletişim bilgileri, telefon, e-posta ve mesaj formu.';
$canonical='https://krpsoft.com.tr/iletisim.php';
$schema=['@context'=>'https://schema.org','@type'=>'Organization','name'=>'Kirpisoft','url'=>'https://krpsoft.com.tr/','email'=>setting('email'),'telephone'=>setting('phone'),'address'=>['@type'=>'PostalAddress','streetAddress'=>setting('address'),'addressLocality'=>'Antalya','addressCountry'=>'TR'],'contactPoint'=>['@type'=>'ContactPoint','telephone'=>setting('phone'),'contactType'=>'customer service','availableLanguage'=>'Turkish']];
$seo='<meta name="robots" content="index,follow,max-image-preview:large">'.
    '<link rel="canonical" href="'.e($canonical).'">'.
    '<meta property="og:type" content="website"><meta property="og:locale" content="tr_TR">'.
    '<meta property="og:site_name" content="Kirpisoft"><meta property="og:title" content="'.e($seoTitle).'">'.
    '<meta property="og:description" content="'.e($seoDescription).'"><meta property="og:url" content="'.e($canonical).'">'.
    '<meta name="twitter:card" content="summary"><meta name="twitter:title" content="'.e($seoTitle).'">'.
    '<meta name="twitter:description" content="'.e($seoDescription).'">'.
    '<script type="application/ld+json">'.json_encode($schema,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>';
$html=preg_replace('/<title>.*?<\/title>/s','<title>'.e($seoTitle).'</title>',$html,1);
$html=preg_replace('/<meta name="description" content="[^"]*">/','<meta name="description" content="'.e($seoDescription).'">',$html,1);
$html=str_replace('</head>',$seo.'</head>',$html);
echo str_replace(array_keys($map),array_values($map),$html);
