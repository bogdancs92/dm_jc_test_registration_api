<?php

namespace App\Security;

/*
 * Password Generator
 * User PHP standard function password_hash with default algorithm
 */
class PasswordEncoder implements PasswordEncoderInterface
{
    const ALGORITHM = PASSWORD_DEFAULT;

    public function encodePassword(string $raw, ?string $salt = null): string
    {
        // As from PHP 7, manualy using salf is deprecated
        return password_hash($raw, self::ALGORITHM);
    }

    /**
     * Check a password.
     */
    public function isPasswordValid(string $encoded, string $raw, ?string $salt = null): bool
    {
        return password_verify($raw, $encoded);
    }
}
