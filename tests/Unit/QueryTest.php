<?php

declare(strict_types=1);

namespace GraphQL\Tests\Unit;

use Generator;
use GraphQL\Exception\ArgumentException;
use GraphQL\Exception\InvalidVariableException;
use GraphQL\InlineFragment;
use GraphQL\OperationType;
use GraphQL\Query;
use GraphQL\RawObject;
use GraphQL\Variable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[CoversClass(Query::class)]
class QueryTest extends TestCase
{
    #[Test]
    #[TestDox('setVariables() must take a list of Variables')]
    public function itCannotSetVariablesFromNonVariableArray(): void
    {
        $sut = new Query('Object');

        self::expectException(InvalidVariableException::class);

        $sut->setVariables(['one', 'two']);
    }

    #[Test]
    #[TestDox('setArguments() MUST receive an array with string keys')]
    public function itCannotSetArgumentsFromList(): void
    {
        $sut = new Query('Object');

        self::expectException(ArgumentException::class);

        $sut->setArguments(['val']);
    }

    #[Test]
    public function itGetsArguments(): void
    {
        $arguments = ['someField' => 'someValue'];
        $sut = (new Query('things'))->setArguments($arguments);

        self::assertSame($arguments, $sut->getArguments());
    }


    /** @param array<mixed> $arguments */
    #[Test, DataProvider('provideInvalidArguments')]
    public function itInvalidatesUnsupportedArguments(array $arguments): void
    {
        $sut = new Query();

        self::expectException(ArgumentException::class);

        $sut->setArguments($arguments);
    }

    #[Test]
    #[DataProvider('provideQueriesToCastToString')]
    public function itIsStringable(string $expected, Query $sut): void
    {
        self::assertSame($expected, $sut->__toString());
    }

    /** @return Generator<array{ 0: array<mixed> }> */
    public static function provideInvalidArguments(): Generator
    {
        yield \DateTime::class => [[new \DateTime()]];

        yield sprintf('nested array of %s', \DateTime::class) => [
            [[[[new \DateTime()]]]],
        ];
    }

    /** @return Generator<array{ 0: string, 1: Query }> */
    public static function provideQueriesToCastToString(): Generator
    {
        yield 'empty query' => ['query', new Query()];

        yield 'without fieldName' => (function () {
            $query = new Query();
            $query->setSelectionSet([
                (new Query('First'))->setSelectionSet(['one']),
                (new Query('Second'))->setSelectionSet(['two'])
            ]);
            return [
                'query { First { one } Second { two } }',
                $query,
            ];
        })();

        yield 'alias set in constructor' => (function () {
            $query = new Query('Object', 'ObjectAlias');
            $query->setSelectionSet(['one']);
            return [
                'query { ObjectAlias: Object { one } }',
                $query,
            ];
        })();

        yield 'alias set after construction' => (function () {
            $query = (new Query('Object'))
                ->setAlias('ObjectAlias')
                ->setSelectionSet([
                    'one'
                ]);
            return [
                'query { ObjectAlias: Object { one } }',
                $query,
            ];
        })();

        yield 'operation name' => (function () {
            $query = (new Query('Object'))
                ->setOperationName('retrieveObject');

            return [
                'query retrieveObject { Object }',
                $query
            ];
        })();

        yield 'operation name and selection set' => (function () {
            $query = (new Query())
                ->setOperationName('retrieveObject')
                ->setSelectionSet([new Query('Object')]);

            return [
                'query retrieveObject { Object }',
                $query
            ];
        })();

        yield 'nested operation name has no effect' => [
            'query retrieveObject { Object { Nested } }',
            (new Query('Object'))
                ->setOperationName('retrieveObject')
                ->setSelectionSet([
                    (new Query('Nested'))
                        ->setOperationName('opName')
                ])
        ];

        yield 'query with one variable' => [
            'query( $var: String ) { Object }',
            (new Query('Object'))
                ->setVariables([new Variable('var', 'String')])
        ];

        yield 'query with two variables' => [
            'query( $var: String $intVar: Int=4 ) { Object }',
            (new Query('Object'))
                ->setVariables([
                    new Variable('var', 'String'),
                    new Variable('intVar', 'Int', false, 4)
                ])
        ];

        yield 'setting variables a second time overwrites the first set' => [
            'query( $secondString: String $secondInt: Int=4 ) { Object }',
            (new Query('Object'))
                ->setVariables([
                    new Variable('firstString', 'String'),
                    new Variable('firstInt', 'Int', false, 4)
                ])
                ->setVariables([
                    new Variable('secondString', 'String'),
                    new Variable('secondInt', 'Int', false, 4)
                ])
        ];

        yield 'operation name and variables' => [
            'query retrieveObject( $var: String ) { Object }',
            (new Query('Object'))
                ->setOperationName('retrieveObject')
                ->setVariables([new Variable('var', 'String')]),
        ];

        yield 'bool argument' => [
            'query { Object(boolArg: true) }',
            (new Query('Object'))->setArguments(['boolArg' => true]),
        ];

        yield 'float argument' => [
            'query { Object(floatArg: 3.14) }',
            (new Query('Object'))->setArguments(['floatArg' => 3.14]),
        ];

        yield 'int argument' => [
            'query { Object(intArg: 34) }',
            (new Query('Object'))->setArguments(['intArg' => 34]),
        ];

        yield 'string argument' => [
            'query { Object(stringArg: "hello world") }',
            (new Query('Object'))->setArguments(['stringArg' => 'hello world']),
        ];

        yield 'null argument' => [
            'query { Object(nullArg: null) }',
            (new Query('Object'))->setArguments(['nullArg' => null]),
        ];

        yield 'int list argument' => [
            'query { Object(intListArg: [1, 2, 3]) }',
            (new Query('Object'))->setArguments(['intListArg' => [1, 2, 3]]),
        ];

        yield 'string list argument' => [
            'query { Object(stringListArg: ["hello", "world"]) }',
            (new Query('Object'))
                ->setArguments(['stringListArg' => ['hello', 'world']]),
        ];

        yield 'nested list argument' => [
            'query { Object(nestedListArg: [[["hello", "world"]]]) }',
            (new Query('Object'))
                ->setArguments(['nestedListArg' => [[['hello', 'world']]]]),
        ];

        yield 'nested list of BackedEnums argument' => [
            'query { Object(nestedEnumArg: [[[query, mutation, subscription]]]) }',
            (new Query('Object'))
                ->setArguments(['nestedEnumArg' => [[[
                    OperationType::Query,
                    OperationType::Mutation,
                    OperationType::Subscription,
                ]]]]),
        ];

        yield 'json object argument' => [
            'query { Object(obj: {json_string_array: ["json value"]}) }',
            (new Query('Object'))
                ->setArguments(['obj' => new RawObject('{json_string_array: ["json value"]}')]),
        ];

        yield 'multiple arguments' => [
            "query { Object(arg1: \"val1\" arg2: 2 arg3: true) }",
            (new Query('Object'))
                ->setArguments(['arg1' => 'val1', 'arg2' => 2, 'arg3' => true]),
        ];
    }

    #[Test]
    public function itOverwritesPreviousSelectionSets()
    {
        $query = (new Query('Object'))
            ->setSelectionSet(['field1'])
            ->setSelectionSet(['field2', 'field3']);
        $this->assertEquals(
            'query { Object { field2 field3 } }',
            (string) $query,
            'Query has improperly formatted selection set'
        );

        return $query;
    }

    public function testTwoLevelQuery()
    {
        $query = (new Query('Object'))
            ->setSelectionSet([
                'field1',
                'field2',
                (new Query('Object2'))
                    ->setSelectionSet(['field3'])
            ]);
        $this->assertEquals(
            "query { Object { field1 field2 Object2 { field3 } } }",
            (string) $query,
            'Two level query not formatted correctly'
        );

        return $query;
    }

    public function testTwoLevelQueryWithInlineFragment()
    {
        $query = (new Query('Object'))
                ->setSelectionSet([
                    'field1',
                    (new InlineFragment('Object'))
                        ->setSelectionSet(
                            [
                                'fragment_field1',
                                'fragment_field2',
                            ]
                        ),
                ]);
        $this->assertEquals(
            'query { Object { field1 ... on Object { fragment_field1 fragment_field2 } } }',
            (string) $query
        );

        return $query;
    }

    public function testGettingArguments()
    {
        $gql = (new Query('things'))
            ->setArguments(
                [
                   'someClientId' => 'someValueBasedOnCodebase'
                ]
            );
        $cursor_id = 'someCursor';
        $new_args = $gql->getArguments();
        $gql->setArguments(
            array_merge(
                $new_args,
                [
                    'after' => $cursor_id
                ]
            )
        );
        self::assertEquals(
            'query { things(someClientId: "someValueBasedOnCodebase" after: "someCursor") }',
            (string) $gql
        );
    }

    public function testGettingNameAndAltering()
    {
        $gql = (new Query('things'))
            ->setSelectionSet(
                [
                    'id',
                    'name',
                    (new Query('subThings'))
                        ->setArguments(
                            [
                                'filter' => 'providerId123',
                            ]
                        )
                        ->setSelectionSet(
                            [
                                'id',
                                'name'
                            ]
                        )
                ]
            );
        $sets = $gql->getSelectionSet();
        foreach ($sets as $set) {
            if (($set instanceof Query) === false) {
                continue;
            }
            $name = $set->getFieldName();
            if ($name !== 'subThings') {
                continue;
            }
            $set->setArguments(
                [
                    'filter' => 'providerId456'
                ]
            );
            $set->setSelectionSet(
                array_merge(
                    $set->getSelectionSet(),
                    [
                        'someField',
                        'someOtherField'
                    ]
                )
            );
        }
        self::assertEquals(
            'query { things { id name subThings(filter: "providerId456") { id name someField someOtherField } } }',
            (string) $gql
        );
    }
}
