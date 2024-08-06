<?php

namespace GraphQL\Tests;

use GraphQL\RawObject;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RawObject::class)]
class RawObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('provideJson')]
    public function itIsStringable(string $json): void
    {
        $sut = new RawObject($json);

        self::assertSame($json, (string) $sut);
    }

    /** @return \Generator<array{0:string}> */
    public static function provideJson(): \Generator
    {
        yield 'array' => [
            '[1, 4, "y", 6.7]',
        ];

        yield 'object' => [
            '{arr: [1, "z"], str: "val", int: 1, obj: {x: "y"}}',
        ];
    }
}
