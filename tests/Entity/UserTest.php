<?php

namespace App\Tests\Entity;

use App\Entity\User;
use DateTime;
use PHPUnit\Framework\TestCase;

/*
 * Test suite for User Entity.
 */
class UserTest extends TestCase
{
    protected $entity;
    protected $id;
    protected $email;
    protected $password;
    protected $registeredAt;
    protected $activated;
    protected $activationCode;
    protected $activationCodeExpireAt;
    protected $activatedAt;

    protected function setUp(): void
    {
        $this->entity = $this->getMockForAbstractClass(User::class);
        $this->id = 55353;
        $this->entity->setId($this->id);
        $this->email = 'john@doe.com';
        $this->entity->setEmail($this->email);
        $this->password = 'pwd';
        $this->entity->setPassword($this->password);
        $this->activated = true;
        $this->entity->setActivated($this->activated);
        $this->registeredAt = new DateTime();
        $this->entity->setRegisteredAt($this->registeredAt);
        $this->activationCode = '1234';
        $this->entity->setActivationCode($this->activationCode);
        $this->activatedAt = new DateTime();
        $this->entity->setActivatedAt($this->activatedAt);
        $this->activationCodeExpireAt = new DateTime('+2 hours');
        $this->entity->setActivationCodeExpireAt($this->activationCodeExpireAt);
    }

    public function testProperties()
    {
        $this->assertSame($this->id, $this->entity->getId());
        $this->assertSame($this->email, $this->entity->getEmail());
        $this->assertSame($this->password, $this->entity->getPassword());
        $this->assertSame($this->activated, $this->entity->getActivated());
        $this->assertEquals($this->registeredAt->getTimestamp(), $this->entity->getRegisteredAt()->getTimestamp());
        $this->assertSame($this->activationCode, $this->entity->getActivationCode());
        $this->assertEquals($this->activatedAt->getTimestamp(), $this->entity->getActivatedAt()->getTimestamp());
        $this->assertEquals($this->activationCodeExpireAt->getTimestamp(), $this->entity->getActivationCodeExpireAt()->getTimestamp());
    }

    public function testSerialization()
    {
        $array = $this->entity->toArray();
        $this->assertSame($this->id, $array['id']);
        $this->assertSame($this->email, $array['email']);
        $this->assertSame($this->password, $array['password']);
        $this->assertSame($this->activated, $array['activated']);
        $this->assertEquals($this->registeredAt->format('Y-m-d H:i:s'), $array['registered_at']);
        $this->assertEquals($this->activatedAt->format('Y-m-d H:i:s'), $array['activated_at']);
        $this->assertEquals($this->activationCodeExpireAt->format('Y-m-d H:i:s'), $array['activation_code_expire_at']);
        $this->assertSame($this->activationCode, $array['activation_code']);
    }

    public function testSerializationValidatedWithContext()
    {
        $array = $this->entity->toArray(User::CONTEXT_PUBLIC);

        $this->assertSame($this->id, $array['id']);
        $this->assertSame($this->email, $array['email']);
        $this->assertArrayNotHasKey('password', $array);
        $this->assertSame($this->activated, $array['activated']);
        $this->assertEquals($this->registeredAt->format('Y-m-d H:i:s'), $array['registered_at']);
        $this->assertEquals($this->activatedAt->format('Y-m-d H:i:s'), $array['activated_at']);
        $this->assertArrayNotHasKey('activation_code', $array);
        $this->assertArrayNotHasKey('activation_code_expire_at', $array);
    }

    public function testSerializationNonValidatedWithContext()
    {
        $this->entity->setActivated(false);
        $array = $this->entity->toArray(User::CONTEXT_PUBLIC);

        $this->assertSame($this->id, $array['id']);
        $this->assertSame($this->email, $array['email']);
        $this->assertArrayNotHasKey('password', $array);
        $this->assertEquals($this->registeredAt->format('Y-m-d H:i:s'), $array['registered_at']);
        $this->assertEquals($this->activationCodeExpireAt->format('Y-m-d H:i:s'), $array['activation_code_expire_at']);
        $this->assertArrayNotHasKey('activation_code', $array);
    }

    public function testUnSerialization()
    {
        $array = [
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->password,
            'activated' => $this->activated,
            'registered_at' => $this->registeredAt->format('Y-m-d H:i:s'),
            'activation_code' => $this->activationCode,
            'activated_at' => $this->activatedAt->format('Y-m-d H:i:s'),
            'activation_code_expire_at' => $this->activationCodeExpireAt->format('Y-m-d H:i:s'),
        ];
        $this->entity->fromArray($array);
        $this->assertSame($this->id, $this->entity->getId());
        $this->assertSame($this->email, $this->entity->getEmail());
        $this->assertSame($this->password, $this->entity->getPassword());
        $this->assertSame($this->activated, $this->entity->getActivated());
        $this->assertEquals($this->registeredAt->getTimestamp(), $this->entity->getRegisteredAt()->getTimestamp());
        $this->assertSame($this->activationCode, $this->entity->getActivationCode());
        $this->assertEquals($this->activatedAt->getTimestamp(), $this->entity->getActivatedAt()->getTimestamp());
        $this->assertEquals($this->activationCodeExpireAt->getTimestamp(), $this->entity->getActivationCodeExpireAt()->getTimestamp());
    }

    public function testPartialUnSerialization()
    {
        $array = [
            'id' => $this->id,
            'activation_code' => $this->activationCode,
        ];
        $this->entity->fromArray($array);
        $this->assertSame($this->id, $this->entity->getId());
        $this->assertSame($this->activationCode, $this->entity->getActivationCode());
        $this->assertEmpty($this->entity->getEmail());
        $this->assertNull($this->entity->getRegisteredAt());
    }
}
