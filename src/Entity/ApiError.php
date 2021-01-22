<?php

namespace App\Entity;

/**
 * Api Error entity returned for any kind of exception.
 */
class ApiError extends BaseEntity
{
    /** @var int */
    private $code;

    /** @var string */
    private $message;

    /**
     * Get the value of message.
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Set the value of message.
     *
     * @return self
     */
    public function setMessage($message): ApiError
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get the value of code.
     */
    public function getCode(): ?int
    {
        return $this->code;
    }

    /**
     * Set the value of code.
     *
     * @return self
     */
    public function setCode($code): ApiError
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Serialization.
     */
    public function toArray(int $context = null): array
    {
        return [
            'error' => [
                'code' => $this->code,
                'message' => $this->message,
            ],
        ];
    }

    /**
     * UnSerialization.
     */
    public function fromArray(array $array): ApiError
    {
        $this->code = $array['error']['code'] ?? null;
        $this->message = $array['error']['message'] ?? null;

        return $this;
    }
}
