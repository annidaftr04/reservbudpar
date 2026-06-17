<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();

    $mail->Host = 'smtp.gmail.com';

    $mail->SMTPAuth = true;

    $mail->Username = 'disbudparreservasi@gmail.com';

    $mail->Password = 'mvphwlcvcwiebjcf';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

    $mail->Port = 465;

    // DEBUG
    $mail->SMTPDebug = 2;

    $mail->setFrom(
        'disbudparreservasi@gmail.com',
        'TEST EMAIL'
    );

    $mail->addAddress(
        'disbudparreservasi@gmail.com'
    );

    $mail->isHTML(true);

    $mail->Subject = 'TEST EMAIL';

    $mail->Body = '<h1>EMAIL TEST BERHASIL</h1>';

    $mail->send();

    echo "EMAIL BERHASIL DIKIRIM";

}

catch (Exception $e) {

    echo "ERROR: " . $mail->ErrorInfo;

}
