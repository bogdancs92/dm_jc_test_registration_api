<?php

namespace App\Mailer;

/*
 * Mailer Interface
 * Used by all mailer services
 */
interface MailerInterface
{
    /**
     * Send an email.
     */
    public function sendMail(string $from, string $to, string $subject, string $body): MailerResponseInterface;
}
