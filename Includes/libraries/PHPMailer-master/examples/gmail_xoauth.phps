<?php

date_default_timezone_set('Etc/UTC');

require '../PHPMailerAutoload.php';


require '../vendor/autoload.php';


$mail = new PHPMailerOAuth;


$mail->isSMTP();


$mail->SMTPDebug = 0;


$mail->Debugoutput = 'html';


$mail->Host = 'smtp.gmail.com';


$mail->Port = 587;


$mail->SMTPSecure = 'tls';


$mail->SMTPAuth = true;


$mail->AuthType = 'XOAUTH2';


$mail->oauthUserEmail = "someone@gmail.com";

$mail->oauthClientId = "RANDOMCHARS-----duv1n2.apps.googleusercontent.com";

$mail->oauthClientSecret = "RANDOMCHARS-----lGyjPcRtvP";


$mail->oauthRefreshToken = "RANDOMCHARS-----DWxgOvPT003r-yFUV49TQYag7_Aod7y0";


$mail->setFrom('from@example.com', 'First Last');


$mail->addAddress('whoto@example.com', 'John Doe');


$mail->Subject = 'PHPMailer GMail SMTP test';


$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));


$mail->AltBody = 'This is a plain-text message body';


$mail->addAttachment('images/phpmailer_mini.png');


if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}
