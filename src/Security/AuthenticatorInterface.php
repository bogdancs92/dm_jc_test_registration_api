<?php

namespace App\Security;

use App\Entity\User;

/*
 * Authenticator Interface
 */
interface AuthenticatorInterface
{
    /**
     * Authenticate the user.
     */
    public function authenticate(): User;
}
