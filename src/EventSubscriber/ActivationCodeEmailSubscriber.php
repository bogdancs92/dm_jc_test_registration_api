<?php

namespace App\EventSubscriber;

use App\Event\UserRegisteredEvent;
use App\Mailer\ExternalMailerService;
use App\Security\CodeGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment;

/*
 * ActivationCodeEmailSubscriber
 * Listen for event user.registered
 * Send email confirmation to user
 */
class ActivationCodeEmailSubscriber implements EventSubscriberInterface
{
    const MAIL_SUBJECT = 'Registration API - Activation Code';
    const MAIL_FROM = 'admin@registration-api.com';

    /** @var LoggerInterface */
    protected $logger;

    /** @var CodeGenerator */
    protected $codeGenerator;

    /** @var ExternalMailerService */
    protected $mailer;

    /** @var Environment */
    protected $twig;

    /**
     * Return the subscribed events, their methods and priorities.
     *
     * @codeCoverageIgnore
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            UserRegisteredEvent::class => 'sendRegistrationActivationCode',
        ];
    }

    public function __construct(LoggerInterface $logger, CodeGenerator $codeGenerator, ExternalMailerService $mailer, Environment $twig)
    {
        $this->logger = $logger;
        $this->codeGenerator = $codeGenerator;
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    /**
     * Send confirmation email.
     */
    public function sendRegistrationActivationCode(UserRegisteredEvent $event)
    {
        // Get user from event
        $user = $event->getUser();

        try {
            // Generate validation code
            $user->setActivated(false);
            $user->setActivationCode($this->codeGenerator->generateCode());
            $user->setActivationCodeExpireAt((new \DateTime())->modify(sprintf('%d seconds', CodeGenerator::CODE_LIFETIME)));

            // Generate email body
            $body = $this->twig->render('emails/confirmationEmail.html.twig', [
                'user' => $user,
            ]);

            // Send Mail
            $this->mailer->sendMail(self::MAIL_FROM, $user->getEmail(), self::MAIL_SUBJECT, $body);
        } catch (\Exception $e) {
            // Error occurred while sending email....
            $this->logger->error(sprintf('%3$s error when sending confirmation mail to user address [%1$s]: [%2$s]', $user->getEmail(), $e->__toString(), __METHOD__));
        }
    }
}
