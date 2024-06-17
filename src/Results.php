<?php

namespace GraphQL;

use GraphQL\Exception\QueryError;
use Psr\Http\Message\ResponseInterface;

class Results
{
    protected string $responseBody;

    /** @var array<?scalar>|object */
    protected null|array|object $results;

    /**
     * Result constructor.
     *
     * Receives json response from GraphQL api response and parses it as associative array or nested object accordingly
     * @throws QueryError
     */
    public function __construct(
        protected ResponseInterface $response,
        bool $asArray = false
    ) {
        $this->responseBody = (string) $this->response->getBody();
        $this->results = json_decode($this->responseBody, $asArray);

        $containsErrors = is_array($this->results) ?
            isset($this->results['errors']) :
            isset($this->results->errors);

        if ($containsErrors) {
            $this->reformatResults(true);
            assert(is_array($this->results));
            throw new QueryError($this->results);
        }
    }

    public function reformatResults(bool $asArray): void
    {
        $this->results = json_decode($this->responseBody, $asArray);
    }

    /**
     * Returns only parsed data objects in the requested format
     *
     * @return array<?scalar>|object
     */
    public function getData()
    {
        return is_array($this->results) ?
            $this->results['data'] ?? [] :
            $this->results->data ?? [];
    }

    /**
     * Returns entire parsed results in the requested format
     *
     * @return null|array<?scalar>|object
     */
    public function getResults(): null|array|object
    {
        return $this->results;
    }

    public function getResponseBody(): string
    {
        return $this->responseBody;
    }

    public function getResponseObject(): ResponseInterface
    {
        return $this->response;
    }
}
