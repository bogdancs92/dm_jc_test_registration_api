<?php

namespace App\Tests\Event;

/*
 * Test suite for UserRegisteredEvent.
 */
use App\Entity\User;
use App\Event\UserRegisteredEvent;
use PHPUnit\Framework\TestCase;
use TypeError;

class UserRegisteredEventTest extends TestCase
{
    public function testGetUser()
    {
        $user = new User();
        $event = new UserRegisteredEvent($user);

        $this->assertEquals($user, $event->getUser());
    }

    public function testMissingUser()
    {
        $this->expectException(TypeError::class);

        $user = new User();
        $event = new UserRegisteredEvent();
    }

    public function testWrongUser()
    {
        $this->expectException(TypeError::class);

        $user = new User();
        $event = new UserRegisteredEvent([]);
    }
}
