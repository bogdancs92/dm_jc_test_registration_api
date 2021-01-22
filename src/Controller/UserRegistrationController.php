<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\UserRegisteredEvent;
use App\Manager\UserManager;
use App\Security\BasicAuthenticator;
use App\Validator\RequestValidatorFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * UserRegistrationController.
 * Contains all controllers to user registration.
 */
class UserRegistrationController extends AbstractController
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var BasicAuthenticator
     */
    protected $authenticator;

    public function __construct(EventDispatcherInterface $dispatcher, UserManager $userManager, BasicAuthenticator $authenticator)
    {
        $this->dispatcher = $dispatcher;
        $this->userManager = $userManager;
        $this->authenticator = $authenticator;
    }

    /**
     * Register Controller.
     *
     * @Route("/users/{version}/register", methods={"POST"}, name="register")
     */
    public function registerAction(Request $request, $version): Response
    {
        // Check if request is valid for this controller.
        // Ensure that all data required to register a new user are there.
        RequestValidatorFactory::createValidator($request)->checkRequest();

        // Request is valid, load a user from request content.
        $user = $this->userManager->createUserFromRequest();

        // Register the user
        $this->userManager->registerUser($user);

        if (false === $user->getActivated()) {
            // User has been registered but require activation
            // Dispatch event user.registered
            $event = new UserRegisteredEvent($user);
            $this->dispatcher->dispatch($event);

            // Persist user : maybe it was updated by event listeners/subscribers
            $this->userManager->updateUser($user);
        }

        // Return response
        return new JsonResponse($user->toArray(User::CONTEXT_PUBLIC));
    }

    /**
     * Activate Controller.
     *
     * @Route("/users/{version}/activate/{activationCode}", methods={"PUT"}, name="activate")
     */
    public function activateAction(Request $request, $version, $activationCode): Response
    {
        // Get authenticated user with Basic Auth
        $user = $this->authenticator->authenticate();

        // Check if request is valid for this controller.
        // Ensure that all data required to verify a user registration are there.
        RequestValidatorFactory::createValidator($request)->checkRequest();

        // Validate the registration
        $this->userManager->activateUser($user, $activationCode);

        // Return response
        return new JsonResponse($user->toArray(User::CONTEXT_PUBLIC));
    }
}
