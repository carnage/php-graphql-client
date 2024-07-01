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
        yield 'nullable string' => [
            '$nullableString: String',
            new Variable('nullableString', 'String'),
        ];

        yield 'nullable string with default' => [
            '$nullableString: String="default"',
            new Variable('nullableString', 'String', false, 'default'),
        ];

        yield 'non-nullable string' => [
            '$nonNullableString: String!',
            new Variable('nonNullableString', 'String', true),
        ];

        yield 'non-nullable string with default' => [
            '$nonNullableString: String!="default"',
            new Variable('nonNullableString', 'String', true, 'default'),
        ];

        yield 'nullable int' => [
            '$nullableInt: Int',
            new Variable('nullableInt', 'Int', false),
        ];

        yield 'nullable int with default' => [
            '$nullableInt: Int=4',
            new Variable('nullableInt', 'Int', false, 4),
        ];

        yield 'nullable string with a numeric-string default' => [
            '$nullableString: String="4"',
            new Variable('nullableString', 'String', false, '4'),
        ];

        yield 'nullable bool with default true' => [
            '$nullableBool: Boolean=true',
            new Variable('nullableBool', 'Boolean', false, true),
        ];

        yield 'nullable bool with default false' => [
            '$nullableBool: Boolean=false',
            new Variable('nullableBool', 'Boolean', false, false),
        ];

        yield 'nullable string with a bool-string default' => [
            '$nullableString: String="true"',
            new Variable('nullableString', 'String', false, 'true'),
        ];

        yield 'nullable int list' => [
            '$nullableIntList: [Int]',
            new Variable('nullableIntList', '[Int]', false, null)
        ];

        yield 'non-nullable int list, default empty array' => [
            '$nonNullableIntList: [Int]!=[]',
            new Variable('nonNullableIntList', '[Int]', true, [])
        ];
    }
}
