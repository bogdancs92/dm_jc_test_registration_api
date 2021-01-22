<?php

namespace App\EventSubscriber;

use App\Entity\ApiError;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/*
 * Exception Subscriber / Listener : Ensure to always return a JSON response
 * when an exception is thrown.
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
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
            KernelEvents::EXCEPTION => 'processException',
        ];
    }

    /**
     * Replace exception response to a JSON stream.
     *
     * @return void
     */
    public function processException(ExceptionEvent $event)
    {
        $response = new Response();
        $exception = $event->getThrowable();

        $apiError = new ApiError();
        $apiError->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        if ($exception instanceof HttpExceptionInterface) {
            // HttpExceptionInterface is a special type of exception that holds status code and header details
            $apiError->setCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        }
        $apiError->setMessage($exception->getMessage());

        // Build response
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($apiError->getCode());
        $response->setContent($apiError->toJson());

        // Replace response with json
        $event->allowCustomResponseCode();
        $event->setResponse($response);
    }
}
