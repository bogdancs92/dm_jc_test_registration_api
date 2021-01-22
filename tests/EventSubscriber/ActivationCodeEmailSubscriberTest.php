<?php

namespace App\Tests\EventListener;

use App\Entity\User;
use App\Event\UserRegisteredEvent;
use App\EventSubscriber\ActivationCodeEmailSubscriber;
use App\Mailer\ExternalMailerService;
use App\Security\CodeGenerator;
use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Twig\Environment;

/*
 * Test suite for ActivationCodeEmailSubscriber
 */
class ActivationCodeEmailSubscriberTest extends TestCase
{
    /** @var LoggerInterface */
    protected $logger;
    /** @var CodeGenerator */
    protected $codeGenerator;
    /** @var ExternalMailerService */
    protected $mailer;
    /** @var Environment */
    protected $twig;
    /** @var ActivationCodeEmailSubscriber */
    protected $listener;

    public function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->codeGenerator = $this->getMockBuilder(CodeGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mailer = $this->getMockBuilder(ExternalMailerService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->twig = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new ActivationCodeEmailSubscriber($this->logger, $this->codeGenerator, $this->mailer, $this->twig);
    }

    public function testSendRegistrationActivationCode()
    {
        $this->codeGenerator->expects($this->once())
            ->method('generateCode')
            ->willReturn('0123');

        $user = new User();
        $user->setEmail('foo@bar.fr');
        $event = new UserRegisteredEvent($user);
        $this->listener->sendRegistrationActivationCode($event);

        $this->assertEquals(false, $user->getActivated());
        $this->assertEquals('0123', $user->getActivationCode());
        $this->assertInstanceOf(DateTime::class, $user->getActivationCodeExpireAt());

        $this->assertEquals(1, 1);
    }

    public function testException()
    {
        $this->mailer->expects($this->once())
            ->method('sendMail')
            ->willThrowException(new Exception("can't send mail"));
        $user = new User();
        $user->setEmail('foo@bar.fr');
        $event = new UserRegisteredEvent($user);

        $anExceptionWasThrown = false;
        try {
            $this->listener->sendRegistrationActivationCode($event);
        } catch (Exception $e) {
            $anExceptionWasThrown = true;
        }

        $this->assertFalse($anExceptionWasThrown);
    }
}
