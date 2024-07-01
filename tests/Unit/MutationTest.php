<?php

namespace GraphQL\Tests\Unit;

use Generator;
use GraphQL\Exception\ArgumentException;
use GraphQL\Exception\InvalidVariableException;
use GraphQL\InlineFragment;
use GraphQL\Mutation;
use GraphQL\RawObject;
use GraphQL\Variable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Mutation::class)]
class MutationTest extends TestCase
{
    #[Test]
    #[TestDox('setVariables() must take a list of Variables')]
    public function itCannotSetVariablesFromNonVariableArray(): void
    {
        $sut = new Mutation('Object');

        self::expectException(InvalidVariableException::class);

        $sut->setVariables(['one', 'two']);
    }

    #[Test]
    #[TestDox('setArguments() MUST receive an array with string keys')]
    public function itCannotSetArgumentsFromList(): void
    {
        $sut = new Mutation('Object');

        self::expectException(ArgumentException::class);

        $sut->setArguments(['val']);
    }

    #[Test]
    public function itGetsArguments(): void
    {
        $arguments = ['someField' => 'someValue'];
        $sut = (new Mutation('things'))->setArguments($arguments);

        self::assertSame($arguments, $sut->getArguments());
    }

    #[Test]
    #[DataProvider('provideMutationsToCastToString')]
    public function itIsStringable(string $expected, Mutation $sut): void
    {
        self::assertSame($expected, $sut->__toString());
    }

    /** @return Generator<array{ 0: string, 1: Mutation }> */
    public static function provideMutationsToCastToString(): Generator
    {
        yield 'empty mutation' => ['mutation', new Mutation()];

        yield 'without fieldName' => (function () {
            $mutation = new Mutation();
            $mutation->setSelectionSet([
                (new Mutation('First'))->setSelectionSet(['one']),
                (new Mutation('Second'))->setSelectionSet(['two'])
            ]);
            return [
                'mutation { First { one } Second { two } }',
                $mutation,
            ];
        })();

        yield 'alias set in constructor' => (function () {
            $mutation = new Mutation('Object', 'ObjectAlias');
            $mutation->setSelectionSet(['one']);
            return [
                'mutation { ObjectAlias: Object { one } }',
                $mutation,
            ];
        })();

        yield 'alias set after construction' => (function () {
            $mutation = (new Mutation('Object'))
                ->setAlias('ObjectAlias')
                ->setSelectionSet([
                    'one'
                ]);
            return [
                'mutation { ObjectAlias: Object { one } }',
                $mutation,
            ];
        })();

        yield 'operation name' => (function () {
            $mutation = (new Mutation('Object'))
                ->setOperationName('retrieveObject');

            return [
                'mutation retrieveObject { Object }',
                $mutation
            ];
        })();

        yield 'operation name and selection set' => (function () {
            $mutation = (new Mutation())
                ->setOperationName('retrieveObject')
                ->setSelectionSet([new Mutation('Object')]);

            return [
                'mutation retrieveObject { Object }',
                $mutation
            ];
        })();

        yield 'nested operation name has no effect' => [
            'mutation retrieveObject { Object { Nested } }',
            (new Mutation('Object'))
                ->setOperationName('retrieveObject')
                ->setSelectionSet([
                    (new Mutation('Nested'))
                        ->setOperationName('opName')
                ])
        ];

        yield 'mutation with one variable' => [
            'mutation( $var: String ) { Object }',
            (new Mutation('Object'))
                ->setVariables([new Variable('var', 'String')])
        ];

        yield 'mutation with two variables' => [
            'mutation( $var: String $intVar: Int=4 ) { Object }',
            (new Mutation('Object'))
                ->setVariables([
                    new Variable('var', 'String'),
                    new Variable('intVar', 'Int', false, 4)
                ])
        ];

        yield 'setting variables a second time overwrites the first set' => [
            'mutation( $secondString: String $secondInt: Int=4 ) { Object }',
            (new Mutation('Object'))
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
            'mutation retrieveObject( $var: String ) { Object }',
            (new Mutation('Object'))
                ->setOperationName('retrieveObject')
                ->setVariables([new Variable('var', 'String')]),
        ];

        yield 'bool argument' => [
            'mutation { Object(boolArg: true) }',
            (new Mutation('Object'))->setArguments(['boolArg' => true]),
        ];

        yield 'float argument' => [
            'mutation { Object(floatArg: 3.14) }',
            (new Mutation('Object'))->setArguments(['floatArg' => 3.14]),
        ];

        yield 'int argument' => [
            'mutation { Object(intArg: 34) }',
            (new Mutation('Object'))->setArguments(['intArg' => 34]),
        ];

        yield 'string argument' => [
            'mutation { Object(stringArg: "hello world") }',
            (new Mutation('Object'))->setArguments(['stringArg' => 'hello world']),
        ];

        yield 'null argument' => [
            'mutation { Object(nullArg: null) }',
            (new Mutation('Object'))->setArguments(['nullArg' => null]),
        ];

        yield 'int list argument' => [
            'mutation { Object(intListArg: [1, 2, 3]) }',
            (new Mutation('Object'))->setArguments(['intListArg' => [1, 2, 3]]),
        ];

        yield 'string list argument' => [
            'mutation { Object(stringListArg: ["hello", "world"]) }',
            (new Mutation('Object'))
                ->setArguments(['stringListArg' => ['hello', 'world']]),
        ];

        yield 'json object argument' => [
            'mutation { Object(obj: {json_string_array: ["json value"]}) }',
            (new Mutation('Object'))
                ->setArguments(['obj' => new RawObject('{json_string_array: ["json value"]}')]),
        ];

        yield 'multiple arguments' => [
            "mutation { Object(arg1: \"val1\" arg2: 2 arg3: true) }",
            (new Mutation('Object'))
                ->setArguments(['arg1' => 'val1', 'arg2' => 2, 'arg3' => true]),
        ];

        yield 'it overwrites previous set selection set' => [
            'mutation { Object { field2 field3 } }',
            (new Mutation('Object'))
                ->setSelectionSet(['field1'])
                ->setSelectionSet(['field2', 'field3']),
        ];

        yield 'nested mutation' => [
            "mutation { Object { field1 field2 Object2 { field3 } } }",
            (new Mutation('Object'))
            ->setSelectionSet([
                'field1',
                'field2',
                (new Mutation('Object2'))
                    ->setSelectionSet(['field3'])
            ])
        ];

        yield 'nested inline fragment' => [
            'mutation { Object { field1 ... on Object { fragment_field1 fragment_field2 } } }',
            (new Mutation('Object'))
                ->setSelectionSet([
                    'field1',
                    (new InlineFragment('Object'))
                        ->setSelectionSet(
                            [
                                'fragment_field1',
                                'fragment_field2',
                            ]
                        ),
                ]),
        ];
    }

    #[Test]
    public function testMutationWithoutOperationType(): void
    {
        $mutation = new Mutation('createObject');

        $this->assertEquals(
            'mutation { createObject }',
            (string) $mutation
        );
    }

    #[Test]
    public function testMutationWithOperationType(): void
    {
        $mutation = new Mutation();
        $mutation
            ->setSelectionSet(
                [
                    (new Mutation('createObject'))
                        ->setArguments(['name' => 'TestObject'])
                ]
            );

        $this->assertEquals(
            'mutation { createObject(name: "TestObject") }',
            (string) $mutation
        );
    }

    #[Test]
    public function testMutationWithoutSelectedFields(): void
    {
        $mutation = (new Mutation('createObject'))
            ->setArguments(['name' => 'TestObject', 'type' => 'TestType']);
        $this->assertEquals(
            'mutation { createObject(name: "TestObject" type: "TestType") }',
            (string) $mutation
        );
    }

    #[Test]
    public function testMutationWithFields(): void
    {
        $mutation = (new Mutation('createObject'))
            ->setSelectionSet(
                [
                    'fieldOne',
                    'fieldTwo',
                ]
            );

        $this->assertEquals(
            'mutation { createObject { fieldOne fieldTwo } }',
            (string) $mutation
        );
    }

    #[Test]
    public function testMutationWithArgumentsAndFields(): void
    {
        $mutation = (new Mutation('createObject'))
            ->setSelectionSet(
                [
                    'fieldOne',
                    'fieldTwo',
                ]
            )->setArguments(
                [
                    'argOne' => 1,
                    'argTwo' => 'val'
                ]
            );

        $this->assertEquals(
            'mutation { createObject(argOne: 1 argTwo: "val") { fieldOne fieldTwo } }',
            (string) $mutation
        );
    }
}
