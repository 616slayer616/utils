<?php

namespace Http\Client\Utils;

use Http\Client\Exception;
use Http\Client\HttpClient;
use Http\Client\Utils\Exception\BatchException;
use Psr\Http\Message\RequestInterface;

/**
 * BatchClient allow to sends multiple request and retrieve a Batch Result
 *
 * This implementation simply loops over the requests and uses sendRequest to send each of them.
 *
 * @author Joel Wurtz <jwurtz@jolicode.com>
 */
class BatchClient implements HttpClient
{
    private $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request)
    {
        return $this->client->sendRequest($request);
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequests(array $requests)
    {
        $batchResult = new BatchResult();

        foreach ($requests as $request) {
            try {
                $response = $this->sendRequest($request);
                $batchResult = $batchResult->addResponse($request, $response);
            } catch (Exception $e) {
                $batchResult = $batchResult->addException($request, $e);
            }
        }

        if ($batchResult->hasExceptions()) {
            throw new BatchException($batchResult);
        }

        return $batchResult;
    }
}
