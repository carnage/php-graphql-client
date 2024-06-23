<?php

namespace GraphQL\Tests\Unit;

use Generator;
use GraphQL\Variable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[CoversClass(Variable::class)]
class VariableTest extends TestCase
{
    #[Test]
    #[DataProvider('provideVariables')]
    public function itIsStringable(string $expected, Variable $sut): void
    {
        self::assertSame($expected, (string) $sut);
    }

    /** @return Generator<array{0:string, 1:Variable}> */
    public static function provideVariables(): Generator
    {
        yield 'optional string' => [
            '$optionalString: String',
            new Variable('optionalString', 'String'),
        ];

        yield 'optional string with default' => [
            '$optionalString: String="default"',
            new Variable('optionalString', 'String', false, 'default'),
        ];

        yield 'required string' => [
            '$requiredString: String!',
            new Variable('requiredString', 'String', true),
        ];

        yield 'required string will ignore default' => [
            '$requiredString: String!',
            new Variable('requiredString', 'String', true, 'def'),
        ];

        yield 'optional int' => [
            '$optionalInt: Int',
            new Variable('optionalInt', 'Int', false),
        ];

        yield 'optional int with default' => [
            '$optionalInt: Int=4',
            new Variable('optionalInt', 'Int', false, 4),
        ];

        yield 'optional string with a numeric-string default' => [
            '$optionalString: String="4"',
            new Variable('optionalString', 'String', false, '4'),
        ];

        yield 'optional bool with default true' => [
            '$optionalBool: Boolean=true',
            new Variable('optionalBool', 'Boolean', false, true),
        ];

        yield 'optional bool with default false' => [
            '$optionalBool: Boolean=false',
            new Variable('optionalBool', 'Boolean', false, false),
        ];

        yield 'optional string with a bool-string default' => [
            '$optionalString: String="true"',
            new Variable('optionalString', 'String', false, 'true'),
        ];
    }
}
