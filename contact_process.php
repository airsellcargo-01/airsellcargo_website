<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = getenv('AIRSELL_SMTP_HOST');
$mail->SMTPAuth = true;
$mail->Username = getenv('AIRSELL_SMTP_USER');
$mail->Password = getenv('AIRSELL_SMTP_PASS');
$mail->SMTPSecure = 'tls';
$mail->Port = (int) getenv('AIRSELL_SMTP_PORT') ?: 587;

$mail->setFrom('info@airsellcargo.com', 'Airsell Cargo');
$mail->addAddress('info@airsellcargo.com');
$mail->Subject = 'Test email';
$mail->Body = 'This is a test.';
$mail->send();
