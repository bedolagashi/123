<?php


require '../PHPMailerAutoload.php';


$pop = POP3::popBeforeSmtp('pop3.example.com', 110, 30, 'username', 'password', 1);


$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    
    $mail->SMTPDebug = 2;
    
    $mail->Debugoutput = 'html';
    
    $mail->Host = "mail.example.com";
   
    $mail->Port = 25;
    
    $mail->SMTPAuth = false;
    
    $mail->setFrom('from@example.com', 'First Last');
   
    $mail->addReplyTo('replyto@example.com', 'First Last');
    
    $mail->addAddress('whoto@example.com', 'John Doe');
    
    $mail->Subject = 'PHPMailer POP-before-SMTP test';
    
    $mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
    
    $mail->AltBody = 'This is a plain-text message body';
    
    $mail->addAttachment('images/phpmailer_mini.png');
    
    $mail->send();
    echo "Message sent!";
} catch (phpmailerException $e) {
    echo $e->errorMessage(); 
} catch (Exception $e) {
    echo $e->getMessage(); 
}
