<?php

date_default_timezone_set('Etc/UTC');

require '../PHPMailerAutoload.php';


$smtp = new SMTP;


$smtp->do_debug = SMTP::DEBUG_CONNECTION;

try {
    
    if (!$smtp->connect('mail.example.com', 25)) {
        throw new Exception('Connect failed');
    }
    
    if (!$smtp->hello(gethostname())) {
        throw new Exception('EHLO failed: ' . $smtp->getError()['error']);
    }
    
    $e = $smtp->getServerExtList();
    
    if (array_key_exists('STARTTLS', $e)) {
        $tlsok = $smtp->startTLS();
        if (!$tlsok) {
            throw new Exception('Failed to start encryption: ' . $smtp->getError()['error']);
        }
        
        if (!$smtp->hello(gethostname())) {
            throw new Exception('EHLO (2) failed: ' . $smtp->getError()['error']);
        }
        
        $e = $smtp->getServerExtList();
    }
    
    if (array_key_exists('AUTH', $e)) {
        if ($smtp->authenticate('username', 'password')) {
            echo "Connected ok!";
        } else {
            throw new Exception('Authentication failed: ' . $smtp->getError()['error']);
        }
    }
} catch (Exception $e) {
    echo 'SMTP error: ' . $e->getMessage(), "\n";
}

$smtp->quit(true);
