<?php

namespace App\Mailer;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/*
 * External Mailer Service
 */
class ExternalMailerService implements MailerInterface
{
    const NB_TRY = 3;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger, string $apiUrl, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
    }

    /**
     * Send an email with external service API.
     */
    public function sendMail(string $from, string $to, string $subject, string $body): ExternalMailerResponse
    {
        // Check parameters
        if (!$this->doCheck($from, $to, $subject, $body)) {
            $this->logger->debug(sprintf('%5$s - Cannot send mail : Invalid parameter FROM=[%1$s] TO=[%2$s] SUBJECT=[%3$s] BODY=[%4$s]', $from, $to, $subject, $body, __METHOD__));

            return new ExternalMailerResponse(MailerResponseInterface::ERROR_PARAMETERS, ['error_message' => 'Missing parameters']);
        }

        $nbCall = 0;
        do {
            $doRetry = false;
            $httpResponse = $this->httpClient->request('POST', $this->apiUrl, ['json' => [
                'api_key' => $this->apiKey,
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
            ]]);

            $serviceStatus = $httpResponse->getStatusCode();
            if ($serviceStatus >= Response::HTTP_INTERNAL_SERVER_ERROR) {
                // Unexpected error. Retry call
                $doRetry = true;
            }

            // Decode & format response
            $response = $this->formatResponse($httpResponse);

            // Pause when doing a retry
            if ($doRetry) {
                sleep(1);
            }
            ++$nbCall;
        } while ($doRetry && $nbCall < self::NB_TRY);

        return $response;
    }

    /**
     * Check parameter before calling external service.
     */
    protected function doCheck(string $from, string $to, string $subject, string $body): bool
    {
        if (empty($from) || empty($to) || empty($subject) || empty($body)) {
            return false;
        }

        return true;
    }

    /**
     * Format service response.
     */
    protected function formatResponse(ResponseInterface $response): ExternalMailerResponse
    {
        $serviceStatus = $response->getStatusCode();
        $rawData = $response->getContent(false);
        $decodedData = json_decode($rawData, true);
        $responseData = [];
        $responseData['http_status'] = $serviceStatus;
        if ($serviceStatus >= Response::HTTP_OK && $serviceStatus < Response::HTTP_MULTIPLE_CHOICES) {
            // Service returned an OK. It must contains an acknowledge id
            $responseStatus = MailerResponseInterface::OK;
            $responseData['ack_id'] = $decodedData['ack_id'];
            $this->logger->info(sprintf('%2$s - Service status=[%1$s] - Mail sent', $serviceStatus, __METHOD__));
        } else {
            // Service returned an error
            if (empty($decodedData) || !is_array($decodedData) || !isset($decodedData['error']) || !isset($decodedData['message'])) {
                // Unknown error formt
                $responseStatus = MailerResponseInterface::ERROR_UNKNOWN;
                $responseData['error_message'] = 'Invalid response format';
                $this->logger->error(sprintf('%3$s - Service status=[%1$s] - Unexpected response format=[%2$s]', $serviceStatus, print_r($decodedData, true), __METHOD__));
            } else {
                // Error with response
                $responseStatus = MailerResponseInterface::ERROR_SEND;
                $responseData['error_code'] = $decodedData['error'];
                $responseData['error_message'] = $decodedData['message'];
                $this->logger->error(sprintf('%3$s - Service status=[%1$s] - Code=[%2$s] Message=[%3$s]', $serviceStatus, $decodedData['error'], $decodedData['message'], __METHOD__));
            }
        }

        return new ExternalMailerResponse($responseStatus, $responseData, $rawData);
    }
}
