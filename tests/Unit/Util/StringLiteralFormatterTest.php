<?php

namespace GraphQL\Tests\Unit\Util;

use Generator;
use GraphQL\Util\StringLiteralFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[CoversClass(StringLiteralFormatter::class)]
class StringLiteralFormatterTest extends TestCase
{
    #[Test]
    #[DataProvider('provideValuesToFormatForRHS')]
    public function itFormatsRHSValues(
        string $expected,
        null|bool|float|int|string $value,
    ): void {
        $actual = StringLiteralFormatter::formatValueForRHS($value);

        self::assertSame($expected, $actual);
    }

    /**
     * @param array<null|bool|float|int|string> $array
     */
    #[Test]
    #[DataProvider('provideArraysToFormatForGQLQueries')]
    public function itFormatsArraysForGQLQueries(
        string $expected,
        array $array,
    ): void {
        $actual = StringLiteralFormatter::formatArrayForGQLQuery($array);

        self::assertSame($expected, $actual);
    }

    #[Test]
    #[DataProvider('provideStringsToFormatToUpperCamelCase')]
    public function itFormatsSnakeCaseToUpperCamelCase(
        string $expected,
        string $stringToFormat,
    ): void {
        $actual = StringLiteralFormatter::formatUpperCamelCase($stringToFormat);

        self::assertSame($expected, $actual);
    }

    #[Test]
    #[DataProvider('provideStringsToFormatToLowerCamelCase')]
    public function itFormatsStringsToLowerCamelCase(
        string $expected,
        string $stringToFormat,
    ): void {
        $actual = StringLiteralFormatter::formatLowerCamelCase($stringToFormat);

        self::assertSame($expected, $actual);
    }

    /** @return Generator<array{0:string, 1:null|bool|float|int|string}> */
    public static function provideValuesToFormatForRHS(): Generator
    {
        yield 'null' => ['null', null];

        yield 'empty string' => ['""', ''];
        yield 'non-empty string' => ['"someString"', 'someString'];
        yield 'unescaped double quotes in string' => [
            '"\"quotedString\""',
            '"quotedString"'
        ];
        yield 'escaped double quotes in string' => [
            '"\\\\\"quotedString\\\\\""',
            '\\"quotedString\\"'
        ];

        yield 'escaped double quoted string in html' => [
            '"<span class=\\\\\"quotedStringEscaped\\\\\" id=\"unescaped\"></span>"',
            '<span class=\\"quotedStringEscaped\\" id="unescaped"></span>',

        ];

        yield 'unescaped single quotes in string' => [
            '"\'singleQuotes\'"',
            "'singleQuotes'",
        ];

        yield 'escaped single quotes in string' => [
            '"\\\\\'singleQuotes\\\\\'"',
            "\\'singleQuotes\\'",
        ];

        yield 'string with newlines' => [
            "\"\"\"with \n newlines\"\"\"",
            "with \n newlines",
        ];

        yield 'string variable name' => ['$var', '$var'];

        yield 'string starts with $ but not a variable' => ['"$400"', '$400'];

        yield 'integer 0' => ['0', 0];

        yield 'integer 25' => ['25', 25];

        yield 'float' => ['3.14', 3.14];

        yield 'bool false' => ['false', false];

        yield 'bool true' => ['true', true];
    }

    /** @return Generator<array{
     *      0:string,
     *      1:array<null|bool|float|int|string>
     * }>
     */
    public static function provideArraysToFormatForGQLQueries(): Generator
    {
        yield 'empty' => ['[]', []];

        yield 'one float' => ['[3.14]', [3.14]];
        yield 'three floats' => ['[3.14, 9.81, 1.67]', [3.14, 9.81, 1.67]];

        yield 'one integer' => ['[1]', [1]];
        yield 'three integers' => ['[1, 2, 3]', [1, 2, 3]];

        yield 'one string' => ['["one"]', ['one']];
        yield 'three strings' => [
            '["one", "two", "three"]',
            ['one', 'two', 'three']
        ];

        yield 'one bool' => ['[true]', [true]];
        yield 'three bools' => ['[true, false, true]', [true, false, true]];
    }

    /** @return Generator<array{0:string, 1:string}> */
    public static function provideStringsToFormatToUpperCamelCase(): Generator
    {
        yield 'some_snake_case' => [
            'SomeSnakeCase',
            'some_snake_case',
        ];

        yield 'lowerCamelCase' => [
            'LowerCamelCase',
            'lowerCamelCase',
        ];

        yield 'UpperCamelCase' => [
            'UpperCamelCase',
            'UpperCamelCase',
        ];
    }

    /** @return Generator<array{0:string, 1:string}> */
    public static function provideStringsToFormatToLowerCamelCase(): Generator
    {
        yield 'some_snake_case' => [
            'someSnakeCase',
            'some_snake_case',
        ];

        yield 'lowerCamelCase' => [
            'lowerCamelCase',
            'lowerCamelCase',
        ];

        yield 'UpperCamelCase' => [
            'upperCamelCase',
            'UpperCamelCase',
        ];
    }
}
