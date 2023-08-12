<?php



date_default_timezone_set('Etc/UTC');

require '../PHPMailerAutoload.php';


$mail = new PHPMailer;


$mail->isSMTP();


$mail->SMTPDebug = 2;


$mail->Debugoutput = 'html';


$mail->Host = 'smtp.example.com';


$mail->Port = 587;


$mail->SMTPSecure = 'tls';


$mail->SMTPOptions = array (
    'ssl' => array(
        'verify_peer'  => true,
        'verify_depth' => 3,
        'allow_self_signed' => true,
        'peer_name' => 'smtp.example.com',
        'cafile' => '/etc/ssl/ca_cert.pem',
    )
);


$mail->SMTPAuth = true;


$mail->Username = "username@example.com";


$mail->Password = "yourpassword";


$mail->setFrom('from@example.com', 'First Last');


$mail->addAddress('whoto@example.com', 'John Doe');


$mail->Subject = 'PHPMailer SMTP options test';


$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));


if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}
