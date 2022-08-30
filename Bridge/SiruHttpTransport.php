<?php
declare(strict_types=1);

namespace Siru\PayumSiru\Bridge;

use Http\Message\MessageFactory;
use Payum\Core\HttpClientInterface;
use Siru\Exception\ApiException;
use Siru\Exception\TransportException;
use Siru\Transport\TransportInterface;

class SiruHttpTransport implements TransportInterface
{

    private string $baseUrl;

    public function __construct(private HttpClientInterface $httpClient, private MessageFactory $messageFactory)
    {}

    public function setBaseUrl(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function request(array $fields, string $endPoint, string $method = 'GET'): array
    {
        $headers = [];
        $body = null;
        $url = $this->baseUrl . $endPoint;

        if ($method === 'GET' || $method === 'DELETE') {
            $url .= ('?' . http_build_query($fields));
        } elseif ($method === 'POST') {
            $headers = ['Contenty-type' => 'application/json', 'Accept' => 'application/json'];
            $body = json_encode($fields);
        }
        $request = $this->messageFactory->createRequest($method, $url, $headers, $body);
        $response = $this->httpClient->send($request);

        if ($response->getStatusCode() === 400) {
            throw ApiException::create($response->getStatusCode(), (string) $response->getBody());
        }
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new TransportException();
        }

        return [
            $response->getStatusCode(),
            (string) $response->getBody()
        ];
    }
}
