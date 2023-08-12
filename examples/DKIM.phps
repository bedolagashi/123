<?php


require '../PHPMailerAutoload.php';


$mail = new PHPMailer;

$mail->setFrom('from@example.com', 'First Last');

$mail->addReplyTo('replyto@example.com', 'First Last');

$mail->addAddress('whoto@example.com', 'John Doe');

$mail->Subject = 'PHPMailer DKIM test';

$mail->DKIM_domain = 'example.com';

$mail->DKIM_private = 'dkim_private.pem';

$mail->DKIM_selector = 'phpmailer';

$mail->DKIM_passphrase = '';

$mail->DKIM_identity = $mail->From;


if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}

