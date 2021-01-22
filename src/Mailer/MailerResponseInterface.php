<?php

namespace App\Mailer;

/*
 * Response Mailer Interface
 * Used by all mailer responses
 */
interface MailerResponseInterface
{
    const OK = 0;                // Mail sent
    const ERROR_PARAMETERS = 1;  // Mail not sent caused by malformed or missing parameter
    const ERROR_SEND = 2;        // Error while sending the mail
    const ERROR_UNKNOWN = 3;     // Unknown error

    /**
     * Get status code.
     */
    public function getStatus(): int;

    /**
     * Get response formatted data.
     * MANDATORY keys :
     *   http_status: the HTTP Status code from the service
     *   error_message : (Only for errors) error message
     * OPTIONAL keys
     *   error_code: error code from service.
     */
    public function getData(): array;

    /**
     * Get raw data.
     */
    public function getRawData(): string;
}
