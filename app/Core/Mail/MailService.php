<?php

namespace App\Core\Mail;

use CodeIgniter\Email\Email;

class MailService
{
    private function mailer(): Email
    {
        $email = \Config\Services::email();
        $email->initialize([
            'protocol'   => 'smtp',
            'SMTPHost'   => setting('mail.smtp_host', 'localhost'),
            'SMTPPort'   => (int) setting('mail.smtp_port', 587),
            'SMTPUser'   => setting('mail.smtp_user', ''),
            'SMTPPass'   => setting('mail.smtp_pass', ''),
            'SMTPCrypto' => setting('mail.encryption', 'tls'),
            'mailType'   => 'html',
            'charset'    => 'UTF-8',
            'newline'    => "\r\n",
        ]);

        return $email;
    }

    public function sendContactNotification(array $message): bool
    {
        $to      = setting('contact.recipient', setting('site.email', ''));
        $from    = setting('mail.from_email', 'noreply@example.com');
        $name    = setting('mail.from_name', 'CekirdekCMS');
        $subject = '[İletişim] ' . ($message['subject'] ?? 'Yeni Mesaj');

        $body = "<p><strong>Ad:</strong> " . esc($message['name']) . "</p>"
              . "<p><strong>E-posta:</strong> " . esc($message['email']) . "</p>"
              . "<p><strong>Konu:</strong> " . esc($message['subject'] ?? '-') . "</p>"
              . "<hr>"
              . "<p>" . nl2br(esc($message['message'])) . "</p>";

        $email = $this->mailer();
        $email->setFrom($from, $name);
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($body);

        return $email->send(false);
    }

    public function sendTestMail(string $to): bool
    {
        $email = $this->mailer();
        $email->setFrom(setting('mail.from_email', 'test@example.com'), setting('mail.from_name', 'CekirdekCMS'));
        $email->setTo($to);
        $email->setSubject('CekirdekCMS Test Maili');
        $email->setMessage('<p>Mail ayarlarınız doğru çalışıyor!</p>');

        return $email->send(false);
    }
}
