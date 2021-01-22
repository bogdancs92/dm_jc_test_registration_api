<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\BasicAuthenticator;
use App\Security\PasswordEncoder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Test suite for Basic Authenticator.
 */
class BasicAuthenticatorTest extends TestCase
{
    /** @var UserRepository */
    protected $repository;

    /** @var PasswordEncoder */
    protected $passwordEncoder;

    /** @var BasicAuthenticator */
    protected $authenticator;

    /** @var User */
    protected $fakeUser;

    protected function setUp(): void
    {
        unset($_SERVER['HTTP_AUTHORIZATION']);
        unset($_SERVER['PHP_AUTH_USER']);
        unset($_SERVER['PHP_AUTH_PW']);

        $this->repository = $this->getMockBuilder(UserRepository::class)
             ->disableOriginalConstructor()
             ->getMock();

        $this->passwordEncoder = $this->getMockBuilder(PasswordEncoder::class)
              ->disableOriginalConstructor()
              ->getMock();

        $this->authenticator = new BasicAuthenticator($this->repository, $this->passwordEncoder);

        $this->fakeUser = new User();
        $this->fakeUser->setEmail('mail');
        $this->fakeUser->setPassword('pwd');
    }

    public function testAuthenticatorAuthenticated()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic FOOBAR';
        $_SERVER['PHP_AUTH_USER'] = 'foo';
        $_SERVER['PHP_AUTH_PW'] = 'foo';

        $this->repository->expects($this->once())
             ->method('findOneBy')
             ->willReturn($this->fakeUser);

        $this->passwordEncoder->expects($this->once())
                         ->method('isPasswordValid')
                         ->willReturn(true);

        $anExceptionWasThrown = false;
        try {
            $user = $this->authenticator->authenticate();
        } catch (Exception $e) {
            $anExceptionWasThrown = true;
        }
        $this->assertFalse($anExceptionWasThrown);
        $this->assertInstanceOf(User::class, $user);
    }

    public function testAuthenticatorWithoutHeader()
    {
        $this->expectException(UnauthorizedHttpException::class);
        $user = $this->authenticator->authenticate();
    }

    public function testAuthenticatorWithoutValidHeader()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic FOOBAR';
        $this->expectException(UnauthorizedHttpException::class);
        $user = $this->authenticator->authenticate();
    }

    public function testAuthenticatorNotKnownUser()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic FOOBAR';
        $_SERVER['PHP_AUTH_USER'] = 'foo';
        $_SERVER['PHP_AUTH_PW'] = 'foo';

        $this->repository->expects($this->once())
                         ->method('findOneBy')
                         ->willReturn(null);

        $this->expectException(UnauthorizedHttpException::class);
        $user = $this->authenticator->authenticate();
    }

    public function testAuthenticatorWrongPassword()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic FOOBAR';
        $_SERVER['PHP_AUTH_USER'] = 'foo';
        $_SERVER['PHP_AUTH_PW'] = 'foo';

        $this->repository->expects($this->once())
                         ->method('findOneBy')
                         ->willReturn($this->fakeUser);

        $this->passwordEncoder->expects($this->once())
          ->method('isPasswordValid')
          ->willReturn(false);

        $this->expectException(UnauthorizedHttpException::class);
        $user = $this->authenticator->authenticate();
    }
}
