<?php
require __DIR__ . '/inc/bootstrap.php';
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $subject=trim($_POST['subject']??''); $message=trim($_POST['message']??'');
    if ($name && filter_var($email,FILTER_VALIDATE_EMAIL) && $message) {
        $s=db()->prepare('INSERT INTO messages(name,email,subject,message) VALUES(?,?,?,?)'); $s->execute([$name,$email,$subject,$message]); $notice='<div class="alert alert-success">Mesajınız alındı. Teşekkür ederiz.</div>';
    } else $notice='<div class="alert alert-danger">Lütfen zorunlu alanları doğru doldurun.</div>';
}
$html=file_get_contents(__DIR__.'/iletisim.html');
$form='<form class="kirpisoft-contact-form" method="post"><input type="hidden" name="csrf" value="'.csrf().'">';
$html=str_replace('<form class="kirpisoft-contact-form" action="mailto:bilgi@kirpii.com" method="post" enctype="text/plain">',$notice.$form,$html);
$html=str_replace(['name="Ad"','name="Email"','name="Konu"','name="Mesaj"'],['name="name" required','name="email" required','name="subject"','name="message" required'],$html);
$map=['İletişim Bilgileri'=>setting('contact_title','İletişim Bilgileri'),'+90 (543) 548 01 22'=>setting('phone'),'+905435480122'=>setting('phone_link'),'bilgi@kirpii.com'=>setting('email'),'iletisim.html'=>'iletisim.php','index.html'=>'index.php'];
echo str_replace(array_keys($map),array_values($map),$html);
