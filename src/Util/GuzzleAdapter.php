<?php

namespace GraphQL\Util;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleAdapter implements Client\ClientInterface
{
    public function __construct(
        private ClientInterface $client
    ) {
    }

    /** @throws GuzzleException */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        /**
         * We are not catching and converting the guzzle exceptions to psr-18 exceptions
         * for backward-compatibility sake
         */

        return $this->client->send($request);
    }
}
