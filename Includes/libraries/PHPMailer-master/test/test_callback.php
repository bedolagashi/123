<html>
<head>
    <title>PHPMailer Lite - DKIM and Callback Function test</title>
</head>
<body>

<?php

function callbackAction($result, $to, $cc, $bcc, $subject, $body)
{
   
    $to = cleanEmails($to, 'to');
    $cc = cleanEmails($cc[0], 'cc');
    $bcc = cleanEmails($bcc[0], 'cc');
    echo $result . "\tTo: " . $to['Name'] . "\tTo: " . $to['Email'] . "\tCc: " . $cc['Name'] .
        "\tCc: " . $cc['Email'] . "\tBcc: " . $bcc['Name'] . "\tBcc: " . $bcc['Email'] .
        "\t" . $subject . "\n\n". $body . "\n";
    return true;
}

require_once '../PHPMailerAutoload.php';
$mail = new PHPMailer();

try {
    $mail->isMail();
    $mail->setFrom('you@example.com', 'Your Name');
    $mail->addAddress('another@example.com', 'John Doe');
    $mail->Subject = 'PHPMailer Test Subject';
    $mail->msgHTML(file_get_contents('../examples/contents.html'));
   
    $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
    $mail->addAttachment('../examples/images/phpmailer.png'); 
    $mail->addAttachment('../examples/images/phpmailer_mini.png'); 
    $mail->action_function = 'callbackAction';
    $mail->send();
    echo "Message Sent OK</p>\n";
} catch (phpmailerException $e) {
    echo $e->errorMessage(); 
} catch (Exception $e) {
    echo $e->getMessage(); 
}

function cleanEmails($str, $type)
{
    if ($type == 'cc') {
        $addy['Email'] = $str[0];
        $addy['Name'] = $str[1];
        return $addy;
    }
    if (!strstr($str, ' <')) {
        $addy['Name'] = '';
        $addy['Email'] = $addy;
        return $addy;
    }
    $addyArr = explode(' <', $str);
    if (substr($addyArr[1], -1) == '>') {
        $addyArr[1] = substr($addyArr[1], 0, -1);
    }
    $addy['Name'] = $addyArr[0];
    $addy['Email'] = $addyArr[1];
    $addy['Email'] = str_replace('@', '&#64;', $addy['Email']);
    return $addy;
}
?>
</body>
</html>
