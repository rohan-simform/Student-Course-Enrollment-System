<?php

require_once __DIR__.'/../vendor/autoload.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();

/**
 * Handles SMTP email sending.
 */
class MailHelper {
    /**
     * Send an email message.
     *
     * @param  string  $email
     * @param  string  $subject
     * @param  string  $body
     * @return bool|string
     */
    public function sendMail($email, $subject, $body) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth = filter_var($_ENV['MAIL_SMTP_AUTH'], FILTER_VALIDATE_BOOLEAN);
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = $_ENV['MAIL_SMTP_SECURE'];
            $mail->Port = (int) $_ENV['MAIL_PORT'];

            $mail->setFrom($_ENV['MAIL_USERNAME'], $_ENV['MAIL_NAME']);
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();

            return true;

        } catch (Exception $e) {
            return $mail->ErrorInfo;
        }
    }
}
