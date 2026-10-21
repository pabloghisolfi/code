<?php

declare(strict_types=1);

namespace Daris\Mail;

/**
 * Envoi d'e-mails texte. En développement (MAIL_ENABLED=false) les messages
 * sont écrits dans var/mail/ au format .eml pour pouvoir les relire.
 */
final class Mailer
{
    public function __construct(
        private readonly bool $enabled,
        private readonly string $from,
        private readonly string $spoolDir
    ) {
    }

    public function send(string $to, string $subject, string $body): bool
    {
        $to = $this->sanitizeHeader($to);
        $subject = $this->sanitizeHeader($subject);

        if (filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        $headers = [
            'From: ' . $this->sanitizeHeader($this->from),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'X-Mailer: Daris',
        ];

        if ($this->enabled) {
            $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

            return mail($to, $encodedSubject, $body, implode("\r\n", $headers));
        }

        return $this->spool($to, $subject, $body, $headers);
    }

    /**
     * @param list<string> $headers
     */
    private function spool(string $to, string $subject, string $body, array $headers): bool
    {
        if (!is_dir($this->spoolDir) && !mkdir($this->spoolDir, 0750, true) && !is_dir($this->spoolDir)) {
            return false;
        }

        $file = $this->spoolDir . '/' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.eml';
        $content = 'To: ' . $to . "\r\n" . 'Subject: ' . $subject . "\r\n" . implode("\r\n", $headers) . "\r\n\r\n" . $body;

        return file_put_contents($file, $content, LOCK_EX) !== false;
    }

    private function sanitizeHeader(string $value): string
    {
        return trim(str_replace(["\r", "\n", "\0"], '', $value));
    }
}
