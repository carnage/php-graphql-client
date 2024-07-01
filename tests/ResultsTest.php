<?php

namespace GraphQL\Tests;

use GraphQL\Exception\QueryError;
use GraphQL\Results;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Results::class)]
class ResultsTest extends TestCase
{
    protected Client $client;

    protected MockHandler $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $this->client      = new Client(['handler' => $this->mockHandler]);
    }

    #[Test]
    public function testGetSuccessResponseAsObject()
    {
        $body = json_encode([
            'data' => [
                'someField' => [
                    [
                        'data' => 'value',
                    ],
                    [
                        'data' => 'value',
                    ]
                ]
            ]
        ]);
        $response = new Response(200, [], $body);
        $this->mockHandler->append($response);

        $response = $this->client->post('', []);
        $results  = new Results($response);

        $this->assertEquals($response, $results->getResponseObject());
        $this->assertEquals($body, $results->getResponseBody());

        $object = new stdClass();
        $object->data = new stdClass();
        $object->data->someField = [];
        $object->data->someField[] = new stdClass();
        $object->data->someField[] = new stdClass();
        $object->data->someField[0]->data = 'value';
        $object->data->someField[1]->data = 'value';
        $this->assertEquals(
            $object,
            $results->getResults()
        );
        $this->assertEquals(
            $object->data,
            $results->getData()
        );
    }

    #[Test]
    public function testGetSuccessResponseAsArray()
    {
        $body = json_encode([
            'data' => [
                'someField' => [
                    [
                        'data' => 'value',
                    ],
                    [
                        'data' => 'value',
                    ]
                ]
            ]
        ]);
        $originalResponse = new Response(200, [], $body);
        $this->mockHandler->append($originalResponse);

        $response = $this->client->post('', []);
        $results  = new Results($response, true);

        $this->assertEquals($originalResponse, $results->getResponseObject());
        $this->assertEquals($body, $results->getResponseBody());
        $this->assertEquals(
            [
                'data' => [
                    'someField' => [
                        [
                            'data' => 'value',
                        ],
                        [
                            'data' => 'value',
                        ]
                    ]
                ]
            ],
            $results->getResults()
        );
        $this->assertEquals(
            [
                'someField' => [
                        [
                            'data' => 'value',
                        ],
                        [
                            'data' => 'value',
                        ]
                    ]
            ],
            $results->getData()
        );
    }

    #[Test]
    public function itThrowsExceptionIfItContainsErrors()
    {
        $body = json_encode([
            'errors' => [
                [
                    'message' => 'some syntax error',
                    'location' => [
                        [
                            'line' => 1,
                            'column' => 3,
                        ]
                    ],
                ]
            ]
        ]);
        $originalResponse = new Response(200, [], $body);
        $this->mockHandler->append($originalResponse);

        $response = $this->client->post('', []);
        $this->expectException(QueryError::class);
        new Results($response);
    }

    #[Test]
    public function testReformatResultsFromObjectToArray()
    {
        $body = json_encode([
            'data' => [
                'someField' => [
                    [
                        'data' => 'value',
                    ],
                    [
                        'data' => 'value',
                    ]
                ]
            ]
        ]);
        $originalResponse = new Response(200, [], $body);
        $this->mockHandler->append($originalResponse);

        $response = $this->client->post('', []);
        $results  = new Results($response);
        $results->reformatResults(true);

        $this->assertEquals(
            [
                'data' => [
                    'someField' => [
                        [
                            'data' => 'value',
                        ],
                        [
                            'data' => 'value',
                        ]
                    ]
                ]
            ],
            $results->getResults()
        );
        $this->assertEquals(
            [
                'someField' => [
                    [
                        'data' => 'value',
                    ],
                    [
                        'data' => 'value',
                    ]
                ]
            ],
            $results->getData()
        );
    }

    #[Test]
    public function testReformatResultsFromArrayToObject()
    {
        $body = json_encode([
            'data' => [
                'someField' => [
                    [
                        'data' => 'value',
                    ],
                    [
                        'data' => 'value',
                    ]
                ]
            ]
        ]);
        $originalResponse = new Response(200, [], $body);
        $this->mockHandler->append($originalResponse);

        $response = $this->client->post('', []);
        $results  = new Results($response, true);
        $results->reformatResults(false);

        $object = new stdClass();
        $object->data = new stdClass();
        $object->data->someField = [];
        $object->data->someField[] = new stdClass();
        $object->data->someField[] = new stdClass();
        $object->data->someField[0]->data = 'value';
        $object->data->someField[1]->data = 'value';
        $this->assertEquals(
            $object,
            $results->getResults()
        );
        $this->assertEquals(
            $object->data,
            $results->getData()
        );
    }
}
