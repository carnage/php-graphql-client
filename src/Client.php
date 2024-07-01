<?php

namespace GraphQL;

use GraphQL\Auth\AuthInterface;
use GraphQL\Exception\QueryError;
use GraphQL\Exception\MethodNotSupportedException;
use GraphQL\QueryBuilder\QueryBuilderInterface;
use GraphQL\Util\GuzzleAdapter;
use GraphQL\Variable;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Client\ClientInterface;

class Client
{
    protected ClientInterface $httpClient;

    /** @var array<string|array<string>> */
    protected array $httpHeaders;

    /**
     * @var array<mixed>
     */
    protected array $options;

    /**
     * Client constructor.
     *
     * @param array<string,string|array<string>> $authorizationHeaders
     * @param array<string,mixed> $httpOptions
     */
    public function __construct(
        protected string $endpointUrl,
        array $authorizationHeaders = [],
        array $httpOptions = [],
        ?ClientInterface $httpClient = null,
        protected string $requestMethod = 'POST',
        protected ?AuthInterface $auth = null
    ) {
        $this->httpHeaders = array_merge(
            $authorizationHeaders,
            $httpOptions['headers'] ?? [],
            ['Content-Type' => 'application/json']
        );

        $this->options = array_filter(
            $httpOptions,
            fn($k) => $k !== 'headers',
            ARRAY_FILTER_USE_KEY,
        );

        $this->httpClient = $httpClient ??
            new GuzzleAdapter(new \GuzzleHttp\Client($httpOptions));

        if ($requestMethod !== 'POST') {
            throw new MethodNotSupportedException($requestMethod);
        }
    }

    /**
     * @param Variable[] $variables
     * @throws QueryError
     */
    public function runQuery(
        Query|QueryBuilderInterface $query,
        bool $resultsAsArray = false,
        array $variables = []
    ): Results {
        $query = $query instanceof QueryBuilderInterface ?
            $query->getQuery() :
            $query;

        return $this->runRawQuery((string) $query, $resultsAsArray, $variables);
    }

    /**
     * @param Variable[] $variables
     * @throws QueryError
     */
    public function runRawQuery(
        string $queryString,
        bool $resultsAsArray = false,
        array $variables = []
    ): Results {
        $request = new Request($this->requestMethod, $this->endpointUrl);

        foreach ($this->httpHeaders as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        // Convert empty variables array to empty json object
        if (empty($variables)) {
            $variables = (object) null;
        }
        // Set query in the request body
        $bodyArray = ['query' => (string) $queryString, 'variables' => $variables];
        $request = $request->withBody(Utils::streamFor(json_encode($bodyArray)));

        if (isset($this->auth)) {
            $request = $this->auth->run($request, $this->options);
        }

        // Send api request and get response
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientException $exception) {
            $response = $exception->getResponse();

            // If exception thrown by client is "400 Bad Request ", then it can be treated as a successful API request
            // with a syntax error in the query, otherwise the exceptions will be propagated
            if ($response->getStatusCode() !== 400) {
                throw $exception;
            }
        }

        // Parse response to extract results
        return new Results($response, $resultsAsArray);
    }
}
