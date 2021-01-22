<?php

namespace App\Mailer;

/*
 * Response of External Mailer Service
 */
class ExternalMailerResponse implements MailerResponseInterface
{
    /**
     * Response status.
     *
     * @var int
     */
    protected $status;

    /**
     * Response formatted data.
     *
     * @var array
     */
    protected $data;

    /**
     * Raw response from service.
     *
     * @var string
     */
    protected $rawData;

    public function __construct(int $status, array $data = [], string $rawData = '')
    {
        $this->status = $status;
        $this->data = $data;
        $this->rawData = $rawData;
    }

    /**
     * Get the value of serviceStatus.
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Get the value of data.
     */
    public function getData(): array
    {
        $data = $this->data;

        // Ensure minimum data
        $data['http_status'] = isset($data['http_status']) ? $data['http_status'] : 0;
        if (self::OK !== $this->getStatus()) {
            $data['error_message'] = isset($data['error_message']) ? $data['error_message'] : '';
        }

        return $data;
    }

    /**
     * Get the value of rawData.
     */
    public function getRawData(): string
    {
        return $this->rawData;
    }
}
