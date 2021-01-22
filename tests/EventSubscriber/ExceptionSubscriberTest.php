<?php

namespace App\Tests\EventListener;

use App\Entity\ApiError;
use App\EventSubscriber\ExceptionSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;

/*
 * Test suite for ExceptionSubscriber
 */
class ExceptionSubscriberTest extends TestCase
{
    /** @var KernelInterface */
    private $kernel;

    /** @var Request */
    private $request;

    public function setUp(): void
    {
        $this->kernel = $this->getMockBuilder(KernelInterface::class)->getMock();
        $this->request = $this->getMockBuilder(Request::class)->getMock();
    }

    public function testHttpException()
    {
        $apiError = new ApiError();
        $apiError->setCode(Response::HTTP_NOT_FOUND);
        $apiError->setMessage('404 Not Found');
        $e = new NotFoundHttpException($apiError->getMessage());
        $event = new ExceptionEvent($this->kernel, $this->request, 1, $e);

        $listener = new ExceptionSubscriber();
        $listener->processException($event);

        $response = $event->getResponse();
        $this->assertSame($apiError->toJson(), $response->getContent());
        $this->assertSame($apiError->getCode(), $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
    }

    public function testException()
    {
        $apiError = new ApiError();
        $apiError->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $apiError->setMessage('My Unexcepted Error');
        $e = new \Exception($apiError->getMessage());
        $event = new ExceptionEvent($this->kernel, $this->request, 1, $e);

        $listener = new ExceptionSubscriber();
        $listener->processException($event);

        $response = $event->getResponse();
        $this->assertSame($apiError->toJson(), $response->getContent());
        $this->assertSame($apiError->getCode(), $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
    }
}
