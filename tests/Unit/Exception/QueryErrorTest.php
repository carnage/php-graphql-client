<?php

namespace GraphQL\Tests\Unit\Exception;

use GraphQL\Exception\QueryError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[CoversClass(QueryError::class)]
class QueryErrorTest extends TestCase
{
    #[Test]
    public function itGetsMessageFromFirstError(): void
    {
        $expected = 'some syntax error';
        $errorDetails = ['errors' => [['message' => $expected]]];

        $sut = new QueryError($errorDetails);

        self::assertEquals($expected, $sut->getMessage());
    }

    #[Test]
    public function itGetsErrorDetails(): void
    {

        $expected = [
            'message' => 'some syntax error',
            'location' => [['line' => 1, 'column' => 3]],
        ];

        $errorDetails = ['errors' => [$expected]];

        $sut = new QueryError($errorDetails);

        self::assertEquals($expected, $sut->getErrorDetails());
    }

    #[Test]
    public function itGetsErrorData(): void
    {
        $expected = [
            'someField' => [['data' => 'firstValue'], ['data' => 'secondValue']]
        ];

        $errorDetails = [
            'errors' => [[
                'message' => 'some syntax error',
                'location' => [['line' => 1, 'column' => 3]],
            ]],
            'data' => $expected,
        ];

        $sut = new QueryError($errorDetails);

        self::assertEquals($expected, $sut->getData());
    }
}
