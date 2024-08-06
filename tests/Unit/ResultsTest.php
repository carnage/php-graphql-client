<?php

namespace GraphQL\Tests\Unit;

use GraphQL\Exception\QueryError;
use GraphQL\Results;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Results::class)]
class ResultsTest extends TestCase
{
    #[Test]
    public function itThrowsExceptionIfItContainsErrors(): void
    {
        $response = new Response(401, [], json_encode([
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
        ]));

        $this->expectException(QueryError::class);

        new Results($response);
    }

    /**
     * @param array{data:array<string,array<array{data:string}>>} $body
     */
    #[Test]
    #[DataProvider('provideResponses')]
    public function itGetsResponses(array $body): void
    {
        $response = new Response(200, [], json_encode($body));
        $results  = new Results($response);

        self::assertEquals($response, $results->getResponseObject());
    }

    /**
     * @param array{data:array<string,array<array{data:string}>>} $body
     */
    #[Test]
    #[DataProvider('provideResponses')]
    public function itGetsResponseBodies(array $body): void
    {
        $response = new Response(200, [], json_encode($body));
        $results  = new Results($response);

        self::assertEquals($response->getBody(), $results->getResponseBody());
    }

    /**
     * @param array{data:array<string,array<array{data:string}>>} $body
     */
    #[Test]
    #[DataProvider('provideResponses')]
    public function itGetsResults(array $body): void
    {
        $response = new Response(200, [], json_encode($body));
        $results  = new Results($response);

        self::assertEquals(json_decode(json_encode($body)), $results->getResults());
    }

    /**
     * @param array{data:array<string,array<array{data:string}>>} $body
     */
    #[Test]
    #[DataProvider('provideResponses')]
    public function itGetsResponseData(array $body): void
    {
        $response = new Response(200, [], json_encode($body));
        $results  = new Results($response);

        self::assertEquals(json_decode(json_encode($body['data']), false), $results->getData());
    }

    /**
     * @return \Generator<array{0: array{
     *     data:array<string,array<array{data:string}>>
     * }}>
     */
    public static function provideResponses(): \Generator
    {
        yield 'one field' => [['data' => [
            'firstField' => [['data' => 'firstValue']]
        ]]];

        yield 'two fields' => [['data' => [
            'firstField' => [['data' => 'firstValue']],
            'secondField' => [['data' => 'secondValue']],
        ]]];

        yield 'one field, two values' => [['data' => [
                'firstField' => [
                    ['data' => 'firstValue'],
                    ['data' => 'secondValue']
                ],
            ]]];
    }

    // #[Test]
    // public function testReformatResultsFromObjectToArray()
    // {
    //     $body = json_encode([
    //         'data' => [
    //             'someField' => [
    //                 [
    //                     'data' => 'value',
    //                 ],
    //                 [
    //                     'data' => 'value',
    //                 ]
    //             ]
    //         ]
    //     ]);
    //     $originalResponse = new Response(200, [], $body);
    //     $this->mockHandler->append($originalResponse);

    //     $response = $this->client->post('', []);
    //     $results  = new Results($response);
    //     $results->reformatResults(true);

    //     $this->assertEquals(
    //         [
    //             'data' => [
    //                 'someField' => [
    //                     [
    //                         'data' => 'value',
    //                     ],
    //                     [
    //                         'data' => 'value',
    //                     ]
    //                 ]
    //             ]
    //         ],
    //         $results->getResults()
    //     );
    //     $this->assertEquals(
    //         [
    //             'someField' => [
    //                 [
    //                     'data' => 'value',
    //                 ],
    //                 [
    //                     'data' => 'value',
    //                 ]
    //             ]
    //         ],
    //         $results->getData()
    //     );
    // }

    // #[Test]
    // public function testReformatResultsFromArrayToObject(): void
    // {
//         $body = json_encode([
//             'data' => [
//                 'someField' => [
//                     [
//                         'data' => 'value',
//                     ],
//                     [
//                         'data' => 'value',
//                     ]
//                 ]
//             ]
//         ]);
//         $originalResponse = new Response(200, [], $body);
//         $this->mockHandler->append($originalResponse);
//
//         $response = $this->client->post('', []);
//         $results  = new Results($response, true);
//         $results->reformatResults(false);
//
//         $object = new stdClass();
//         $object->data = new stdClass();
//         $object->data->someField = [];
//         $object->data->someField[] = new stdClass();
//         $object->data->someField[] = new stdClass();
//         $object->data->someField[0]->data = 'value';
//         $object->data->someField[1]->data = 'value';
    //     self::assertEquals(
    //         $object,
    //         $results->getResults()
    //     );
    //     self::assertEquals(
    //         $object->data,
    //         $results->getData()
    //     );
    // }
}
