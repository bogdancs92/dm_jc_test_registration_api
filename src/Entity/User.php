<?php

namespace App\Entity;

/**
 * User Entity.
 */
class User extends BaseEntity
{
    /**
     * Serialization context for public fields.
     */
    const CONTEXT_PUBLIC = 1;

    /**
     * Email address.
     *
     * @var string
     */
    private $email;

    /**
     * Password.
     *
     * @var string
     */
    private $password;

    /**
     * Registration date time.
     *
     * @var \DateTime
     */
    private $registeredAt;

    /**
     * User is activated.
     *
     * @var bool
     */
    private $activated;

    /**
     * Activation Code.
     *
     * @var string
     */
    private $activationCode;

    /**
     * Activation Code expiry DateTime.
     *
     * @var \DateTime
     */
    private $activationCodeExpireAt;

    /**
     * Activation Date Time.
     *
     * @var \DateTime
     */
    private $activatedAt;

    /**
     * Get email address.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email address.
     *
     * @param string $email email address
     *
     * @return self
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password.
     *
     * @param string $password password
     *
     * @return self
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get registration date time.
     */
    public function getRegisteredAt(): ?\DateTime
    {
        return $this->registeredAt;
    }

    /**
     * Set registration date time.
     *
     * @param \DateTime $registeredAt Registered DateTime
     *
     * @return self
     */
    public function setRegisteredAt(?\DateTime $registeredAt): User
    {
        $this->registeredAt = $registeredAt;

        return $this;
    }

    /**
     * Get user is activated.
     *
     * @return bool
     */
    public function getActivated()
    {
        return $this->activated;
    }

    /**
     * Set user activated.
     *
     * @param bool $validated user is active
     *
     * @return self
     */
    public function setActivated(bool $activated): User
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * Get Activation Code.
     */
    public function getActivationCode(): string
    {
        return $this->activationCode;
    }

    /**
     * Set activation Code.
     *
     * @return self
     */
    public function setActivationCode(string $activationCode): User
    {
        $this->activationCode = $activationCode;

        return $this;
    }

    /**
     * Get Activation code expiry DateTime.
     */
    public function getActivationCodeExpireAt(): ?\DateTime
    {
        return $this->activationCodeExpireAt;
    }

    /**
     * Set Activation code expiry DateTime.
     *
     * @param \DateTime $activationCodeExpireAt code expiry Date Time
     *
     * @return self
     */
    public function setActivationCodeExpireAt(?\DateTime $activationCodeExpireAt): User
    {
        $this->activationCodeExpireAt = $activationCodeExpireAt;

        return $this;
    }

    /**
     * Get activation Date Time.
     *
     * @return DateTime
     */
    public function getActivatedAt(): ?\DateTime
    {
        return $this->activatedAt;
    }

    /**
     * Set activation Date Time.
     *
     * @param \DateTime $activatedAt activation Date Time
     *
     * @return self
     */
    public function setActivatedAt(?\DateTime $activatedAt): User
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    /**
     * Map Entity to array.
     * Without context, whole entity is returned.
     *
     * @param int $context conversion context to use
     */
    public function toArray(int $context = null): array
    {
        $entityAsArray = [
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->password,
            'registered_at' => $this->registeredAt ? $this->registeredAt->format('Y-m-d H:i:s') : null,
            'activated' => $this->activated,
            'activated_at' => $this->activatedAt ? $this->activatedAt->format('Y-m-d H:i:s') : null,
            'activation_code' => $this->activationCode,
            'activation_code_expire_at' => $this->activationCodeExpireAt ? $this->activationCodeExpireAt->format('Y-m-d H:i:s') : null,
        ];
        if ($context & self::CONTEXT_PUBLIC === 1) {
            // Remove null values
            $entityAsArray = array_filter($entityAsArray, function ($value) {
                return null !== $value;
            });

            // Remove protected fields
            unset($entityAsArray['password']);
            unset($entityAsArray['activation_code']);
            if ($this->activated) {
                unset($entityAsArray['activation_code_expire_at']);
            }
        }

        return $entityAsArray;
    }

    /**
     * UnSerialization.
     */
    public function fromArray(array $array): User
    {
        $this->id = $array['id'] ?? null;
        $this->email = $array['email'] ?? null;
        $this->password = $array['password'] ?? null;
        $this->registeredAt = isset($array['registered_at']) ? new \DateTime($array['registered_at']) : null;
        $this->activated = $array['activated'] ?? false;
        $this->activatedAt = isset($array['activated_at']) ? new \DateTime($array['activated_at']) : null;
        $this->activationCode = $array['activation_code'] ?? null;
        $this->activationCodeExpireAt = isset($array['activation_code_expire_at']) ? new \DateTime($array['activation_code_expire_at']) : null;

        return $this;
    }
}
