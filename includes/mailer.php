<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

$vendorAutoload = ROOT_PATH . '/vendor/autoload.php';
if (is_file($vendorAutoload)) {
    require_once $vendorAutoload;
}

if (!function_exists('send_email')) {
    function send_email(string $toEmail, string $toName, string $subject, string $htmlBody, string $plainBody = ''): bool
    {
        $mailConfig = app_config('mail', []);
        $fromEmail = (string) ($mailConfig['from_email'] ?? 'no-reply@example.com');
        $fromName = (string) ($mailConfig['from_name'] ?? 'Website');

        if (class_exists(PHPMailer::class)) {
            try {
                $mail = new PHPMailer(true);

                if (!empty($mailConfig['use_smtp'])) {
                    $mail->isSMTP();
                    $mail->Host = (string) ($mailConfig['smtp_host'] ?? '');
                    $mail->Port = (int) ($mailConfig['smtp_port'] ?? 587);
                    $mail->SMTPAuth = true;
                    $mail->Username = (string) ($mailConfig['smtp_user'] ?? '');
                    $mail->Password = (string) ($mailConfig['smtp_pass'] ?? '');
                    $mail->SMTPSecure = (string) ($mailConfig['smtp_secure'] ?? PHPMailer::ENCRYPTION_STARTTLS);
                }

                $mail->setFrom($fromEmail, $fromName);
                $mail->addAddress($toEmail, $toName);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $htmlBody;
                $mail->AltBody = $plainBody !== '' ? $plainBody : strip_tags($htmlBody);

                return $mail->send();
            } catch (Throwable) {
                return false;
            }
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $fromName . ' <' . $fromEmail . '>',
        ];

        return mail($toEmail, $subject, $htmlBody, implode("\r\n", $headers));
    }
}
