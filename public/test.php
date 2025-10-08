<?php


// function smtp_mail($to, $subject, $message, $headers) {
//     $smtp_server = "smtp.office365.com"; // Your SMTP server
//     $smtp_port = 587; // SMTP port (465 for SSL, 587 for TLS)
//     $smtp_user = "coziclubsupport@luxinnerwear.com";
//     $smtp_pass = "sdvqkytrkgnsjvwl";

//     $socket = fsockopen($smtp_server, $smtp_port, $errno, $errstr, 30);
//     if (!$socket) {
//         die("Failed to connect: $errno - $errstr");
//     }

//     fputs($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
//     fputs($socket, "AUTH LOGIN\r\n");
//     fputs($socket, base64_encode($smtp_user) . "\r\n");
//     fputs($socket, base64_encode($smtp_pass) . "\r\n");

//     fputs($socket, "MAIL FROM: <$smtp_user>\r\n");
//     fputs($socket, "RCPT TO: <$to>\r\n");
//     fputs($socket, "DATA\r\n");
//     fputs($socket, "Subject: $subject\r\n");
//     fputs($socket, "$headers\r\n\r\n");
//     fputs($socket, "$message\r\n.\r\n");
//     fputs($socket, "QUIT\r\n");

//     fclose($socket);
// }

// $to = "priyamlk2505@gmail.com";
// $subject = "Test Email via SMTP";
// $message = "This is a test email using raw SMTP commands.";
// $headers = "From: coziclubsupport@luxinnerwear.com\r\n";

// smtp_mail($to, $subject, $message, $headers);
// echo "Email sent!";


// $to = "priyamlk2505@gmail.com";
// $subject = "Test Email via Gmail SMTP";
// $message = "This is a test email sent using Gmail SMTP.";
// $headers = "From: Your Name <coziclubsupport@luxinnerwear.com>\r\n" .
//           "Reply-To: coziclubsupport@luxinnerwear.com\r\n" .
//           "MIME-Version: 1.0\r\n" .
//           "Content-Type: text/html; charset=UTF-8\r\n";

// // SMTP Configuration
// $smtp_server = "smtp.office365.com";
// $smtp_port = 587;
// $smtp_user = "coziclubsupport@luxinnerwear.com";
// $smtp_pass = "sdvqkytrkgnsjvwl";

// $smtp_conn = fsockopen($smtp_server, $smtp_port, $errno, $errstr, 30);
// if (!$smtp_conn) {
//     die("Could not connect to SMTP server: $errno - $errstr");
// }

// fputs($smtp_conn, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
// fputs($smtp_conn, "AUTH LOGIN\r\n");
// fputs($smtp_conn, base64_encode($smtp_user) . "\r\n");
// fputs($smtp_conn, base64_encode($smtp_pass) . "\r\n");
// fputs($smtp_conn, "MAIL FROM: <$smtp_user>\r\n");
// fputs($smtp_conn, "RCPT TO: <$to>\r\n");
// fputs($smtp_conn, "DATA\r\n");
// fputs($smtp_conn, "Subject: $subject\r\n$headers\r\n\r\n$message\r\n.\r\n");
// fputs($smtp_conn, "QUIT\r\n");
// fclose($smtp_conn);

// echo "Email sent!";


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp-legacy.office365.com';
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = 'tls';
    $mail->Username   = 'admin@foxandmandal.co.in'; // Your full Office365 email
    $mail->Password   = "Sumit@2025$";             // App Password (Not normal password)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

   // Allow self-signed certs (required for some shared hosts)
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ];

    // Email headers
    $mail->setFrom('admin@foxandmandal.co.in', 'LuxCozi Club');
    $mail->addAddress('priya.m@techmantra.co'); // To email
    $mail->isHTML(true);
    $mail->Subject = 'Test Email via Office365 SMTP';
    $mail->Body    = 'This is a test email sent using Office365 SMTP from PHP.';

    $mail->send();
    echo '✅ Email sent successfully!';
} catch (Exception $e) {
    echo "❌ Email could not be sent. Error: {$mail->ErrorInfo}";
}





?>
