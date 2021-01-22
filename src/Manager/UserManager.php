<?php

namespace App\Manager;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\CodeGenerator;
use App\Security\PasswordEncoder;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;

/*
 * User Manager
 * Contains all business logic about user management
 */
class UserManager
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var PasswordEncoder
     */
    private $passwordEncoder;

    /**
     * @var CodeGenerator
     */
    private $codeGenerator;

    public function __construct(
        UserRepository $userRepository,
        RequestStack $requestStack,
        PasswordEncoder $passwordEncoder,
        CodeGenerator $codeGenerator
    ) {
        $this->userRepository = $userRepository;
        $this->request = $requestStack->getCurrentRequest();
        $this->passwordEncoder = $passwordEncoder;
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * Get User Repository.
     */
    public function getUserRepository(): UserRepository
    {
        return $this->userRepository;
    }

    /**
     * Create a User instance from request data.
     */
    public function createUserFromRequest(): User
    {
        $rawContent = $this->request->getContent();
        $data = json_decode($rawContent, true);
        $user = new User();
        if (null === $data) {
            return $user;
        }

        return $user->fromArray($data);
    }

    /**
     * Register a new user.
     *
     * @throws ConflictHttpException
     * @throws Exception
     */
    public function registerUser(User $user)
    {
        // Check is email is already registered
        $existingUser = $this->userRepository->findOneBy(['email' => $user->getEmail()]);
        if (null !== $existingUser) {
            // Email already exists, throw a 409 Conflict
            throw new ConflictHttpException(sprintf('User with email [%1$s] already exists', $user->getEmail()));
        }

        // Encode the Password
        $user->setPassword($this->passwordEncoder->encodePassword($user->getPassword()));

        // Set registered data
        $user->setRegisteredAt(new DateTime());
        $user->setActivated(false);

        // Persist user
        $this->updateUser($user);
    }

    /**
     * Activate the user by checking activation code.
     *
     * @throws GoneHttpException
     * @throws Exception
     */
    public function activateUser(User $user, string $activationCode)
    {
        $now = new \DateTime();
        if ($user->getActivated()) {
            // User already active, throw a 409 Conflict
            throw new ConflictHttpException('User already active');
        }

        if (!$user->getActivationCode() || !$user->getActivationCodeExpireAt()) {
            // User does not have activation code nor activation code expiry
            // This should never happen in real life
            throw new ConflictHttpException('User not ready for activation');
        }

        if (!$this->codeGenerator->compareCodes($user->getActivationCode(), $activationCode)) {
            // Code invalid, throw a 400 Bad request
            throw new BadRequestHttpException('Invalid activation code');
        }

        if ($now > $user->getActivationCodeExpireAt()) {
            // Code expired, throw a 410 Gone
            throw new GoneHttpException('Activation code has expired');
        }

        // Set validation data
        $user->setActivatedAt($now);
        $user->setActivated(true);

        // Persist user
        $this->updateUser($user);
    }

    /**
     * Update / Insert user in repository.
     *
     * @throws Exception
     */
    public function updateUser(User $user)
    {
        $this->userRepository->persist($user);
    }
}
