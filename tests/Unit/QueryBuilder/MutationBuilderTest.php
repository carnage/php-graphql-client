<?php

namespace GraphQL\Tests\Unit\QueryBuilder;

use GraphQL\InlineFragment;
use GraphQL\Mutation;
use GraphQL\QueryBuilder\AbstractQueryBuilder;
use GraphQL\QueryBuilder\MutationBuilder;
use GraphQL\QueryBuilder\QueryBuilderInterface;
use GraphQL\RawObject;
use GraphQL\Variable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[CoversClass(MutationBuilder::class)]
#[CoversClass(AbstractQueryBuilder::class)]
class MutationBuilderTest extends TestCase
{
    #[Test]
    public function itCanBuildMutationWithoutName()
    {
        $builder = (new MutationBuilder())
            ->selectField(
                (new MutationBuilder('Object'))
                ->selectField('one')
            )
            ->selectField(
                (new MutationBuilder('Another'))
                    ->selectField('two')
            );

        $this->assertEquals(
            'mutation { Object { one } Another { two } }',
            (string) $builder->getMutation()
        );
    }


    /**
     * @param array<InlineFragment|Mutation|QueryBuilderInterface> $selectionSet
     * @param Variable[] $variables
     * @param array<string,string> $arguments
     */
    #[Test]
    #[DataProvider('provideDataToBuildMutation')]
    public function itBuildsQueries(
        string $name,
        string $alias = '',
        array $selectionSet = [],
        array $variables = [],
        array $arguments = [],
    ): void {
        $expected = (new Mutation($name, $alias))
            ->setSelectionSet($selectionSet)
            ->setVariables($variables)
            ->setArguments($arguments);

        $sut = new MutationBuilder($name, $alias);

        foreach ($selectionSet as $selection) {
            $sut->selectField($selection);
        }

        foreach ($variables as $variable) {
            $sut->setVariable(
                $variable->name,
                $variable->type,
                $variable->nonNullable,
                $variable->defaultValue,
            );
        }

        foreach ($arguments as $argumentName => $argumentValue) {
            $sut->setArgument($argumentName, $argumentValue);
        }

        self::assertEquals($expected, $sut->getMutation());
    }

    /** @return \Generator<array{
     *     0: string,
     *     1?: string,
     *     2?: array<InlineFragment|Mutation|QueryBuilderInterface>,
     *     3?: Variable[],
     *     4?: array<string,mixed>,
     * }>
     */
    public static function provideDataToBuildMutation(): \Generator
    {
        yield 'minimal mutation' => ['Test'];

        yield 'alias' => ['Test', 'Test_Alias'];

        yield 'one selection' => ['One_Selection', '', ['first']];

        yield 'three selections' => [
            'three_selections',
            '',
            ['first', 'second', 'third'],
        ];

        yield sprintf('%s selection', InlineFragment::class) => [
            'WithInlineFragmentSelection',
            '',
            [(new InlineFragment('Nested'))->setSelectionSet(['field'])],
        ];

        yield sprintf('%s selection', Mutation::class) => [
            'WithMutationSelection',
            '',
            [(new Mutation('Nested'))->setSelectionSet(['some_field'])],
        ];

        yield sprintf('%s selection', MutationBuilder::class) => [
            'WithMutationBuilderSelection',
            '',
            [(new MutationBuilder('Nested'))->selectField('fieldTwo')],
        ];

        yield 'one variable' => [
            'one_variable',
            '',
            [],
            [new Variable('first_var', 'String', true, 'default string')],
        ];

        yield 'three variables' => [
            'ThreeVariables',
            '',
            [],
            [
                new Variable('first_var', 'String', true, 'default string'),
                new Variable('second_var', 'Int', true, 5),
                new Variable('third_var', 'Array', true, [1, 2, 4]),
            ],
        ];

        yield 'one argument' => [
            'one_argument',
            '',
            [],
            [],
            ['string_argument' => 'value']
        ];

        yield 'three arguments' => [
            'Three_Arguments',
            '',
            [],
            [],
            [
                'string_argument' => 'value',
                'int_argument' => 1,
                'object_argument' => new RawObject('{field_not: "x"}'),
            ]
        ];
    }
}
