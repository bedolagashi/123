<?php

error_reporting(E_STRICT | E_ALL);

date_default_timezone_set('Etc/UTC');

require '../PHPMailerAutoload.php';

$mail = new PHPMailer;

$body = file_get_contents('contents.html');

$mail->isSMTP();
$mail->Host = 'smtp.example.com';
$mail->SMTPAuth = true;
$mail->SMTPKeepAlive = true; 
$mail->Port = 25;
$mail->Username = 'yourname@example.com';
$mail->Password = 'yourpassword';
$mail->setFrom('list@example.com', 'List manager');
$mail->addReplyTo('list@example.com', 'List manager');

$mail->Subject = "PHPMailer Simple database mailing list test";


$mail->msgHTML($body);

$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';


$mysql = mysqli_connect('localhost', 'username', 'password');
mysqli_select_db($mysql, 'mydb');
$result = mysqli_query($mysql, 'SELECT full_name, email, photo FROM mailinglist WHERE sent = false');

foreach ($result as $row) { 
    $mail->addAddress($row['email'], $row['full_name']);
    if (!empty($row['photo'])) {
        $mail->addStringAttachment($row['photo'], 'YourPhoto.jpg'); 
    }

    if (!$mail->send()) {
        echo "Mailer Error (" . str_replace("@", "&#64;", $row["email"]) . ') ' . $mail->ErrorInfo . '<br />';
        break; 
    } else {
        echo "Message sent to :" . $row['full_name'] . ' (' . str_replace("@", "&#64;", $row['email']) . ')<br />';
        
        mysqli_query(
            $mysql,
            "UPDATE mailinglist SET sent = true WHERE email = '" .
            mysqli_real_escape_string($mysql, $row['email']) . "'"
        );
    }
    
    $mail->clearAddresses();
    $mail->clearAttachments();
}
