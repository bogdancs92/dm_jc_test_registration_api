<?php

/*
 * Mock of an external mail service
 * Only log content into stdin
 * Used by Email service in Registration API
 */

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExternalMailServiceMockController extends AbstractController
{
    /**
     * @var LoggerInterface
     */
    private $mailerLogger;

    public function __construct(LoggerInterface $mailerLogger)
    {
        $this->mailerLogger = $mailerLogger;
    }

    /**
     * @Route("/external/mail/send", methods={"POST"}, name="mock_mail_send")
     */
    public function sendMailAction(Request $request): Response
    {
        $content = json_decode($request->getContent(), true);
        if (null === $content || empty($content['body'])) {
            return new JsonResponse(['error' => Response::HTTP_BAD_REQUEST, 'message' => 'missing email body'], Response::HTTP_BAD_REQUEST);
        }

        $this->mailerLogger->info($content['body']);
        $ackId = md5((string) mt_rand());

        return new JsonResponse(['status' => 'OK', 'ack_id' => $ackId], Response::HTTP_OK);
    }
}
