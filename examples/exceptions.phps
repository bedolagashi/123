<?php


require '../PHPMailerAutoload.php';


$mail = new PHPMailer(true);
try {
    
    $mail->setFrom('from@example.com', 'First Last');
    
    $mail->addReplyTo('replyto@example.com', 'First Last');
    
    $mail->addAddress('whoto@example.com', 'John Doe');
    
    $mail->Subject = 'PHPMailer Exceptions test';
    
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
