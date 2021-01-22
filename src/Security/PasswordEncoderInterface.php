<?php

namespace App\Security;

/*
 * Password Encoder Interface
 */
interface PasswordEncoderInterface
{
    /**
     * Encode a password.
     */
    public function encodePassword(string $raw, ?string $salt = null): string;

    /**
     * Check a password.
     */
    public function isPasswordValid(string $encoded, string $raw, ?string $salt = null): bool;
}
