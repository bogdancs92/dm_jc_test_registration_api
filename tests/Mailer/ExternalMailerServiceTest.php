<?php

use App\Mailer\ExternalMailerResponse;
use App\Mailer\ExternalMailerService;
use App\Mailer\MailerResponseInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

/*
 * Test suite for External email service
 */
class ExternalMailerServiceTest extends TestCase
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testSendMailOk()
    {
        $rawData = json_encode(['ack_id' => 'ack']);
        $responses = [
            new MockResponse(json_encode(['ack_id' => 'ack']), ['http_code' => Response::HTTP_OK]),
        ];
        $httpClient = new MockHttpClient($responses);
        $mailer = new ExternalMailerService($httpClient, $this->logger, 'http://url/', 'key');
        // Send Mail
        $response = $mailer->sendMail('from', 'to', 'subject', 'body');
        // Expect mail was sent
        $this->assertInstanceOf(ExternalMailerResponse::class, $response);
        $this->assertSame(MailerResponseInterface::OK, $response->getStatus());
        $responseData = $response->getData();
        $this->assertSame('ack', $responseData['ack_id']);
        $this->assertSame(Response::HTTP_OK, $responseData['http_status']);
        $this->assertSame($rawData, $response->getRawData());
    }

    public function testSendMailWithWrongParameters()
    {
        $httpClient = new MockHttpClient();
        $mailer = new ExternalMailerService($httpClient, $this->logger, 'http://url/', 'key');
        // Send Mail
        $response = $mailer->sendMail('from', 'to', 'subject', '');
        // Expect failure
        $this->assertInstanceOf(ExternalMailerResponse::class, $response);
        $this->assertSame(MailerResponseInterface::ERROR_PARAMETERS, $response->getStatus());
        $responseData = $response->getData();
        $this->assertNotEmpty($responseData['error_message']);
    }

    public function testSendMailKnownError()
    {
        $rawData = json_encode(['error' => Response::HTTP_BAD_REQUEST, 'message' => 'Bad request']);
        $responses = [
            new MockResponse($rawData, ['http_code' => Response::HTTP_BAD_REQUEST]),
        ];
        $httpClient = new MockHttpClient($responses);
        $mailer = new ExternalMailerService($httpClient, $this->logger, 'http://url/', 'key');
        //Send mail
        $response = $mailer->sendMail('from', 'to', 'subject', 'body');
        // Expected failure
        $this->assertInstanceOf(ExternalMailerResponse::class, $response);
        $this->assertSame(MailerResponseInterface::ERROR_SEND, $response->getStatus());
        $this->assertSame($rawData, $response->getRawData());
        $responseData = $response->getData();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseData['http_status']);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseData['error_code']);
        $this->assertSame('Bad request', $responseData['error_message']);
    }

    public function testSendMailUnknownError()
    {
        $rawData = json_encode(['another_data' => 'foo']);
        $responses = [
            new MockResponse($rawData, ['http_code' => Response::HTTP_BAD_REQUEST]),
        ];
        $httpClient = new MockHttpClient($responses);
        $mailer = new ExternalMailerService($httpClient, $this->logger, 'http://url/', 'key');

        // Send Mail
        $response = $mailer->sendMail('from', 'to', 'subject', 'body');
        // Expected failure
        $this->assertInstanceOf(ExternalMailerResponse::class, $response);
        $this->assertSame(MailerResponseInterface::ERROR_UNKNOWN, $response->getStatus());
        $this->assertSame($rawData, $response->getRawData());

        $responseData = $response->getData();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $responseData['http_status']);
        $this->assertNotEmpty($responseData['error_message']);
    }

    public function testSendMailErrorWithRetry()
    {
        $responses = [
            new MockResponse(json_encode(['error' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => 'Server down']), ['http_code' => Response::HTTP_INTERNAL_SERVER_ERROR]),
            new MockResponse(json_encode(['error' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => 'Server down']), ['http_code' => Response::HTTP_INTERNAL_SERVER_ERROR]),
            new MockResponse(json_encode(['error' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => 'Server down']), ['http_code' => Response::HTTP_INTERNAL_SERVER_ERROR]),
        ];
        $httpClient = new MockHttpClient($responses);
        $mailer = new ExternalMailerService($httpClient, $this->logger, 'http://url/', 'key');

        // Send Mail
        $response = $mailer->sendMail('from', 'to', 'subject', 'body');
        // Expected failure
        $this->assertInstanceOf(ExternalMailerResponse::class, $response);
        $this->assertSame(MailerResponseInterface::ERROR_SEND, $response->getStatus());
        $responseData = $response->getData();
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $responseData['http_status']);
        $this->assertSame('Server down', $responseData['error_message']);
    }

    public function testSendMailOKWithRetry()
    {
        $responses = [
            new MockResponse(json_encode(['error' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => 'Server down']), ['http_code' => Response::HTTP_INTERNAL_SERVER_ERROR]),
            new MockResponse(json_encode(['ack_id' => 'ack']), ['http_code' => Response::HTTP_OK]),
        ];
        $httpClient = new MockHttpClient($responses);
        $mailer = new ExternalMailerService($httpClient, $this->logger, 'http://url/', 'key');

        // Send Mail
        $response = $mailer->sendMail('from', 'to', 'subject', 'body');
        // Expected mail was sent
        $this->assertInstanceOf(ExternalMailerResponse::class, $response);
        $this->assertSame(MailerResponseInterface::OK, $response->getStatus());
    }
}
