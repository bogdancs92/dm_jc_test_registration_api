<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event "User is Regitered".
 * Contains the user entity that was just registered.
 */
class UserRegisteredEvent extends Event
{
    public const NAME = 'user.registered';

    /**
     * @var User
     */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
