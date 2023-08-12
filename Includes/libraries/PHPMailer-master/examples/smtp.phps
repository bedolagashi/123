<?php

date_default_timezone_set('Etc/UTC');

require '../PHPMailerAutoload.php';


$mail = new PHPMailer;

$mail->isSMTP();

$mail->SMTPDebug = 2;

$mail->Debugoutput = 'html';

$mail->Host = "mail.example.com";

$mail->Port = 25;

$mail->SMTPAuth = true;

$mail->Username = "yourname@example.com";

$mail->Password = "yourpassword";

$mail->setFrom('from@example.com', 'First Last');

$mail->addReplyTo('replyto@example.com', 'First Last');

$mail->addAddress('whoto@example.com', 'John Doe');

$mail->Subject = 'PHPMailer SMTP test';

$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));

$mail->AltBody = 'This is a plain-text message body';

$mail->addAttachment('images/phpmailer_mini.png');

if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}
