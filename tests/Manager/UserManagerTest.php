<?php

namespace App\Tests\Manager;

use App\Entity\User;
use App\Manager\UserManager;
use App\Repository\UserRepository;
use App\Security\CodeGenerator;
use App\Security\PasswordEncoder;
use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use TypeError;

/*
 * Test suite for UserManager
 */
class UserManagerTest extends TestCase
{
    /** @var RequestStack */
    protected $requestStack;
    /** @var Request */
    protected $request;
    /** @var UserRepository */
    protected $repository;
    /** @var PasswordEncoder */
    protected $passwordEncoder;
    /** @var CodeGenerator */
    protected $codeGenerator;
    /** @var UserManager */
    protected $userManager;

    protected function setUp(): void
    {
        $body = [
            'email' => 'john@doe.com',
            'password' => 'password',
        ];
        $this->request = new Request([], [], [], [], [], [], json_encode($body));

        $this->requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestStack->expects($this->any())
            ->method('getCurrentRequest')
            ->willReturn($this->request);

        $this->repository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->passwordEncoder = $this->getMockBuilder(PasswordEncoder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->codeGenerator = $this->getMockBuilder(CodeGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->userManager = new UserManager($this->repository, $this->requestStack, $this->passwordEncoder, $this->codeGenerator);
    }

    public function testGetRepository()
    {
        $this->assertEquals($this->repository, $this->userManager->getUserRepository());
    }

    public function testCreateUserFromRequest()
    {
        $user = new User();
        $user->fromArray([
            'email' => 'john@doe.com',
            'password' => 'password',
        ]);
        $newUser = $this->userManager->createUserFromRequest();
        $this->assertEquals($user->toArray(), $newUser->toArray());
    }

    public function testCreateUserFromEmptyRequest()
    {
        $emptyUser = new User();
        $this->request = new Request();

        $this->requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestStack->expects($this->any())
            ->method('getCurrentRequest')
            ->willReturn($this->request);

        $this->userManager = new UserManager($this->repository, $this->requestStack, $this->passwordEncoder, $this->codeGenerator);

        $newUser = $this->userManager->createUserFromRequest();
        $this->assertEquals($emptyUser->toArray(), $newUser->toArray());
    }

    public function testUpdateUser()
    {
        $user = new User();
        $user->fromArray([
            'email' => 'john@doe.com',
            'password' => 'password',
        ]);

        $this->repository->expects($this->once())
            ->method('persist');

        $this->userManager->updateUser($user);
    }

    public function testUpdateUserWithoutUser()
    {
        $this->expectException(TypeError::class);
        $this->userManager->updateUser();
    }

    public function testUpdateUserWithWrongUser()
    {
        $this->expectException(TypeError::class);
        $this->userManager->updateUser([]);
    }

    public function testRegisterUser()
    {
        $user = new User();
        $user->setEmail('john@doe.com');
        $user->setPassword('password');

        $encodedPassword = 'PWD';
        $this->passwordEncoder->expects($this->once())
            ->method('encodePassword')
            ->willReturn($encodedPassword);

        $foundUser = null;
        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($foundUser);

        $anExceptionWasThrown = false;
        try {
            $this->userManager->registerUser($user);
        } catch (Exception $e) {
            $anExceptionWasThrown = true;
        }
        $this->assertFalse($anExceptionWasThrown);
        $this->assertSame($encodedPassword, $user->getPassword());
        $this->assertFalse($user->getActivated());
        $this->assertInstanceOf(DateTime::class, $user->getRegisteredAt());
    }

    public function testRegisterUserExisting()
    {
        $user = new User();
        $user->setEmail('john@doe.com');
        $user->setPassword('password');

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->willReturn(new User());

        $this->expectException(ConflictHttpException::class);
        $res = $this->userManager->registerUser($user);
    }

    public function testRegisterUserWithoutUser()
    {
        $this->expectException(TypeError::class);
        $this->userManager->registerUser();
    }

    public function testRegisterUserWithWrongUser()
    {
        $this->expectException(TypeError::class);
        $this->userManager->registerUser('foo');
    }

    public function testActivateUser()
    {
        $activationCode = 'ABCD';
        $user = new User();
        $user->setEmail('john@doe.com');
        $user->setPassword('password');
        $user->setActivationCode('ABCD');
        $user->setActivationCodeExpireAt((new DateTime())->modify('+'.(CodeGenerator::CODE_LIFETIME * 2).' seconds'));
        $user->setActivated(false);

        $this->codeGenerator->expects($this->once())
                              ->method('compareCodes')
                              ->willReturn(true);

        $anExceptionWasThrown = false;
        try {
            $this->userManager->activateUser($user, $activationCode);
        } catch (Exception $e) {
            $anExceptionWasThrown = true;
        }
        $this->assertFalse($anExceptionWasThrown);
        $this->assertTrue($user->getActivated());
        $this->assertInstanceOf(DateTime::class, $user->getActivatedAt());
    }

    public function testActivateUserAlreadyActive()
    {
        $activationCode = 'ABCD';
        $user = new User();
        $user->setEmail('john@doe.com');
        $user->setPassword('password');
        $user->setActivationCode('ABCD');
        $user->setActivationCodeExpireAt((new DateTime())->modify('+'.(CodeGenerator::CODE_LIFETIME * 2).' seconds'));
        $user->setActivated(true);

        $this->expectException(ConflictHttpException::class);
        $this->userManager->activateUser($user, $activationCode);
    }

    public function testActivateUserWithoutActivationCode()
    {
        $activationCode = 'ABCD';
        $user = new User();
        $user->setEmail('john@doe.com');
        $user->setPassword('password');
        $user->setActivationCode('');
        $user->setActivationCodeExpireAt((new DateTime())->modify('+'.(CodeGenerator::CODE_LIFETIME * 2).' seconds'));
        $user->setActivated(false);

        $this->expectException(ConflictHttpException::class);
        $this->userManager->activateUser($user, $activationCode);
    }

    public function testActivateUserWithoutActivationCodeExpiry()
    {
        $activationCode = 'ABCD';
        $user = new User();
        $user->setEmail('john@doe.com');
        $user->setPassword('password');
        $user->setActivationCode('ABCD');
        $user->setActivationCodeExpireAt(null);
        $user->setActivated(false);

        $this->expectException(ConflictHttpException::class);
        $this->userManager->activateUser($user, $activationCode);
    }

    public function testActivateUserCodeExpired()
    {
        $activationCode = 'ABCD';
        $user = new User();
        $user->setEmail('john@doe.com');
        $user->setPassword('password');
        $user->setActivationCode('ABCD');
        $user->setActivationCodeExpireAt((new DateTime())->modify('-'.CodeGenerator::CODE_LIFETIME.' seconds'));
        $user->setActivated(false);
        $this->codeGenerator->expects($this->once())
                              ->method('compareCodes')
                              ->willReturn(true);

        $this->expectException(GoneHttpException::class);
        $this->userManager->activateUser($user, $activationCode);
    }

    public function testActivateUserWrongCode()
    {
        $activationCode = 'ABCD';
        $user = new User();
        $user->setEmail('john@doe.com');
        $user->setPassword('password');
        $user->setActivationCode('ABCD');
        $user->setActivationCodeExpireAt((new DateTime())->modify('+'.(CodeGenerator::CODE_LIFETIME * 2).' seconds'));
        $user->setActivated(false);

        $this->codeGenerator->expects($this->once())
                            ->method('compareCodes')
                            ->willReturn(false);

        $this->expectException(BadRequestHttpException::class);
        $this->userManager->activateUser($user, $activationCode);
    }

    public function testActivateUserWithoutUser()
    {
        $this->expectException(TypeError::class);
        $this->userManager->activateUser();
    }
}
