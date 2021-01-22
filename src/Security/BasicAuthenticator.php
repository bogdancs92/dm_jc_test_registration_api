<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/*
 * Basic Authenticator
 * Use behavior proposed by PHP that automatically fill PHP_AUTH_USER & PHP_AUTH_PW
 * with decoded token information
 */
class BasicAuthenticator implements AuthenticatorInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var PasswordEncoder
     */
    private $passwordEncoder;

    public function __construct(UserRepository $userRepository, PasswordEncoder $passwordEncoder)
    {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Get user authenticated with a basic token.
     *
     * @throws UnauthorizedHttpException
     * @throws Exception
     */
    public function authenticate(): User
    {
        // Check auth type
        if (!preg_match('/^Basic\s/', ($_SERVER['HTTP_AUTHORIZATION'] ?? ''))) {
            throw new UnauthorizedHttpException('Basic realm="Access denied"', 'Authentication token missing');
        }

        // Check basic credentials from request
        if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
            throw new UnauthorizedHttpException('Basic realm="Access denied"', 'Authentication token missing');
        }

        // Get email used in basic token
        $email = $_SERVER['PHP_AUTH_USER'];

        // Get user from repository
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (null === $user) {
            // Email unknown, throw a 401 Unauthorized
            throw new UnauthorizedHttpException('Basic realm="Access denied"', 'Invalid credentials');
        }

        // Check password
        if (!$this->passwordEncoder->isPasswordValid($user->getPassword(), $_SERVER['PHP_AUTH_PW'])) {
            // Wrong password, throw a 401 Unauthorized
            throw new UnauthorizedHttpException('Basic realm="Access denied"', 'Invalid credentials');
        }

        return $user;
    }
}
