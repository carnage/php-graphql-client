<?php

namespace GraphQL\Tests\Unit\QueryBuilder;

use GraphQL\InlineFragment;
use GraphQL\Query;
use GraphQL\QueryBuilder\AbstractQueryBuilder;
use GraphQL\QueryBuilder\QueryBuilder;
use GraphQL\QueryBuilder\QueryBuilderInterface;
use GraphQL\RawObject;
use GraphQL\Variable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(QueryBuilder::class)]
#[CoversClass(AbstractQueryBuilder::class)]
class QueryBuilderTest extends TestCase
{
    #[Test]
    public function testDagger(): void
    {

        $rootQb = new QueryBuilder();
        $queryStack = [];

        $pipeline = (new QueryBuilder('pipeline'))
            ->setArgument('name', 'test')
            ->setArgument('description', 'pipeline description')
            ->setArgument('labels', [new RawObject('{distribution: alpine}')]);
        $queryStack[] = $pipeline;

        $container = (new QueryBuilder('container'));
        $queryStack[] = $container;


        $from = (new QueryBuilder('from'))
            ->setArgument('address', 'alpine:3.16.2');
        $queryStack[] = $from;

        $withExec = (new QueryBuilder('withExec'))
            ->setArgument('args', ['cat', '/etc/alpine-release']);
        $queryStack[] = $withExec;

        $queryStack[] = new QueryBuilder('stdout');

        foreach ($queryStack as $queryBuilder) {
            $rootQb = $rootQb->selectField($queryBuilder);
        }

        self::assertSame(
            'query { pipeline' .
                '(' .
                'name: "test" ' .
                'description: "pipeline description" ' .
                'labels: [{distribution: alpine}]' .
                ') ' .
                'container ' .
                'from(address: "alpine:3.16.2") ' .
                'withExec(args: ["cat", "/etc/alpine-release"]) ' .
                'stdout }',
            (string) $rootQb->getQuery()
        );
    }

    #[Test]
    public function itCanBuildQueryWithoutName(): void
    {
        $builder = (new QueryBuilder())
            ->selectField(
                (new QueryBuilder('Object'))
                ->selectField('one')
            )
            ->selectField(
                (new QueryBuilder('Another'))
                    ->selectField('two')
            );

        $this->assertEquals(
            'query { Object { one } Another { two } }',
            (string) $builder->getQuery()
        );
    }


    /**
     * @param array<InlineFragment|Query|QueryBuilderInterface> $selectionSet
     * @param Variable[] $variables
     * @param array<string,string> $arguments
     */
    #[Test]
    #[DataProvider('provideDataToBuildQuery')]
    public function itBuildsQueries(
        string $name,
        string $alias = '',
        array $selectionSet = [],
        array $variables = [],
        array $arguments = [],
    ): void {
        $expected = (new Query($name, $alias))
            ->setSelectionSet($selectionSet)
            ->setVariables($variables)
            ->setArguments($arguments);

        $sut = new QueryBuilder($name, $alias);

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

        self::assertEquals($expected, $sut->getQuery());
    }

    /** @return \Generator<array{
     *     0: string,
     *     1?: string,
     *     2?: array<InlineFragment|Query|QueryBuilderInterface>,
     *     3?: Variable[],
     *     4?: array<string,mixed>,
     * }>
     */
    public static function provideDataToBuildQuery(): \Generator
    {
        yield 'minimal query' => ['Test'];

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

        yield sprintf('%s selection', Query::class) => [
            'WithQuerySelection',
            '',
            [(new Query('Nested'))->setSelectionSet(['some_field'])],
        ];

        yield sprintf('%s selection', QueryBuilder::class) => [
            'WithQueryBuilderSelection',
            '',
            [(new QueryBuilder('Nested'))->selectField('fieldTwo')],
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
