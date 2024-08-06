<?php

namespace GraphQL\Tests;

use GraphQL\Client;
use GraphQL\Exception\MethodNotSupportedException;
use GraphQL\Exception\QueryError;
use GraphQL\Query;
use GraphQL\QueryBuilder\QueryBuilder;
use GraphQL\QueryBuilder\QueryBuilderInterface;
use GraphQL\RawObject;
use GraphQL\Variable;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

#[CoversClass(Client::class)]
class ClientTest extends TestCase
{
    #[Test]
    #[DataProvider('provideUnsuccessfulResponses')]
    public function itThrowsExceptionsForUnsuccessfulResponses(
        string $expectedExceptionClass,
        mixed $response,
    ): void {
        $handler = new MockHandler();
        $handler->append($response);
        $handlerStack = HandlerStack::create($handler);
        $client = new Client('', [], ['handler' => $handlerStack]);

        $this->expectException($expectedExceptionClass);

        $client->runRawQuery('');
    }

    public static function provideUnsuccessfulResponses(): \Generator
    {
        yield '200 with syntax error' => [
            QueryError::class,
            new Response(200, [], json_encode([
                'errors' => [
                    [
                        'message' => 'some syntax error',
                        'location' => [
                            [
                                'line' => 1,
                                'column' => 3,
                            ],
                        ],
                    ],
                ],
            ])),
        ];

        yield 'ClientException: 400 with syntax error' => [
            QueryError::class,
            new ClientException(
                '',
                new Request('post', ''),
                new Response(400, [], json_encode([
                    'errors' => [
                        [
                            'message' => 'some syntax error',
                            'location' => [
                                [
                                    'line' => 1,
                                    'column' => 3,
                                ],
                            ],
                        ],
                    ],
                ])),
            ),
        ];

        yield 'ClientException: 401 Unauthorized' => [
            ClientException::class,
            new ClientException(
                '',
                new Request('post', ''),
                new Response(401, [], '"Unauthorized"')
            ),
        ];

        yield 'ClientException: 404 Not Found' => [
            ClientException::class,
            new ClientException(
                '',
                new Request('post', ''),
                new Response(404, [], '"Not Found"')
            ),
        ];

        yield 'ServerException: 500 Server Error' => [
            ServerException::class,
            new ServerException(
                '',
                new Request('post', ''),
                new Response(500, [], '"Server Error"')
            ),
        ];

        yield 'ConnectException: Time Out' => [
            ConnectException::class,
            new ConnectException('Time Out', new Request('post', '')),
        ];
    }


    #[Test]
    public function itOnlySupportsPostRequests(): void
    {
        self::expectException(MethodNotSupportedException::class);

        new Client('', [], [], null, 'GET');
    }

    #[Test]
    public function itReturnsResultsContainingResponse(): void
    {
        $expected = new Response(200, [], json_encode([
            'data' => ['firstField' => [['data' => 'value']]],
        ]));

        $handler = new MockHandler();
        $handler->append($expected);
        $handlerStack = HandlerStack::create($handler);

        $sut = new Client('', [], ['handler' => $handlerStack]);

        $actual = $sut->runRawQuery('')->getResponseObject();

        self::assertEquals($expected, $actual);
    }

    /**
     * @param array<string,string> $variables
     * @param array<string|array<string>> $headers
     */
    #[Test]
    #[DataProvider('provideQueries')]
    public function itSendsQueries(
        string $expectedQueryString,
        Query|QueryBuilderInterface $query,
        string $method,
        array $variables,
        array $headers,
    ): void {
        $handler = new MockHandler();
        $handler->append(new Response(200));
        $handlerStack = HandlerStack::create($handler);

        $sut = new Client(
            '',
            $headers,
            ['handler' => $handlerStack],
            null,
            $method,
        );

        $sut->runQuery($query, false, $variables);

        self::assertSame(
            $expectedQueryString,
            (string) $handler->getLastRequest()->getBody(),
        );

        self::assertSame(
            $method,
            $handler->getLastRequest()->getMethod(),
        );

        foreach ($headers as $header => $value) {
            self::assertSame(
                is_string($value) ? [$value] : $value,
                $handler->getLastRequest()->getHeader($header),
            );
        }
    }

    /**
     * @param array<string,string> $variables
     * @param array<string|array<string>> $headers
     */
    #[Test]
    #[DataProvider('provideRawQueries')]
    public function itSendsRawQueries(
        string $expectedQueryString,
        string $rawQueryString,
        string $method,
        array $variables,
        array $headers,
    ): void {
        $handler = new MockHandler();
        $handler->append(new Response(200));
        $handlerStack = HandlerStack::create($handler);

        $sut = new Client(
            '',
            $headers,
            ['handler' => $handlerStack],
            null,
            $method,
        );

        $sut->runRawQuery($rawQueryString, false, $variables);

        self::assertSame(
            $expectedQueryString,
            (string) $handler->getLastRequest()->getBody(),
        );

        self::assertSame(
            $method,
            $handler->getLastRequest()->getMethod(),
        );

        foreach ($headers as $header => $value) {
            self::assertSame(
                is_string($value) ? [$value] : $value,
                $handler->getLastRequest()->getHeader($header),
            );
        }
    }


    /** @return \Generator<array{
     *     0: string,
     *     1: Query|QueryBuilderInterface,
     *     2: string,
     *     3: array<string,string>,
     *     4: array<string|array<string>>,
     *  }>
     */
    public static function provideQueries(): \Generator
    {
        yield 'minimal query' => [
            '{"query":"query","variables":{}}',
            new Query(),
            'POST',
            [],
            [],
        ];

        yield 'minimal query builder' => [
            '{"query":"query","variables":{}}',
            new QueryBuilder(),
            'POST',
            [],
            [],
        ];

        yield 'one variable' => [
            '{"query":"query","variables":{"name":"value"}}',
            new Query(),
            'POST',
            ['name' => 'value'],
            [],
        ];

        yield 'one variable in query' => [
            '{"query":"query( $name: string )","variables":{}}',
            (new Query())->setVariables([new Variable('name', 'string')]),
            'POST',
            [],
            [],
        ];

        yield 'authorization header' => [
            '{"query":"query","variables":{}}',
            new Query(),
            'POST',
            [],
            ['Authorization' => 'Basic xyz'],
        ];
    }

    /** @return \Generator<array{
     *     0: string,
     *     1: string,
     *     2: string,
     *     3: array<string,string>,
     *     4: array<string|array<string>>,
     *  }>
     */
    public static function provideRawQueries(): \Generator
    {
        yield 'minimal raw query' => [
            '{"query":"query_string","variables":{}}',
            'query_string',
            'POST',
            [],
            [],
        ];

        yield 'one variable' => [
            '{"query":"query_string","variables":{"name":"value"}}',
            'query_string',
            'POST',
            ['name' => 'value'],
            [],
        ];

        yield 'authorization header' => [
            '{"query":"query_string","variables":{}}',
            'query_string',
            'POST',
            [],
            ['Authorization' => 'Basic xyz'],
        ];
    }

    #[Test]
    public function testValidQueryResponseToArray(): void
    {
        $handler = new MockHandler();
        $handler->append(new Response(200, [], json_encode([
            'data' => [
                'someField' => [
                    [
                        'data' => 'value',
                    ], [
                        'data' => 'value',
                    ],
                ],
            ],
        ])));

        $handlerStack = HandlerStack::create($handler);
        $sut = new Client('', [], ['handler' => $handlerStack]);

        $arrayResults = $sut->runRawQuery('', true);
        $this->assertIsArray($arrayResults->getResults());
    }
}
