<?php

namespace GraphQL\Tests\Unit;

use GraphQL\InlineFragment;
use GraphQL\Query;
use GraphQL\QueryBuilder\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InlineFragment::class)]
class InlineFragmentTest extends TestCase
{
    #[Test]
    #[DataProvider('provideInlineFragmentsCastToString')]
    public function itIsStringable(string $expected, InlineFragment $sut): void
    {
        self::assertSame($expected, (string)$sut);
    }

    /** @return \Generator<array{0:string, 1:InlineFragment}>*/
    public static function provideInlineFragmentsCastToString(): \Generator
    {
        yield 'minimal example' => [
            '... on minimal',
            new InlineFragment('minimal'),
        ];

        yield 'one selection' => [
            '... on OneSelection { field1 }',
            (new InlineFragment('OneSelection'))->setSelectionSet(['field1']),
        ];

        yield 'three selections' => [
            '... on ThreeSelections { field1 field2 field3 }',
            (new InlineFragment('ThreeSelections'))
                ->setSelectionSet(['field1', 'field2', 'field3']),
        ];

        yield 'minimal subquery selection' => [
            '... on query_selection { sub_query }',
            (new InlineFragment('query_selection'))->setSelectionSet([
                (new Query('sub_query'))
            ]),
        ];

        yield 'subquery (with args) selection ' => [
            '... on query_selection { sub_query(firstArg: 5) }',
            (new InlineFragment('query_selection'))->setSelectionSet([
                (new Query('sub_query'))->setArguments(['firstArg' => 5]),
            ]),
        ];


        yield 'subquery (with subselection) selection ' => [
            '... on query_selection { sub_query { sub_selection } }',
            (new InlineFragment('query_selection'))->setSelectionSet([
                (new Query('sub_query'))->setSelectionSet(['sub_selection']),
            ]),
        ];

        yield 'subquery (with sub-fragment selection) selection ' => [
            '... on query_selection { sub_query { ... on Sub_Sub_Fragment { sub_sub_sub_field } } }',
            (new InlineFragment('query_selection'))->setSelectionSet([
                (new Query('sub_query'))->setSelectionSet([
                    (new InlineFragment('Sub_Sub_Fragment'))
                        ->setSelectionSet(['sub_sub_sub_field']),
                ]),
            ]),
        ];

        yield 'minimal querybuilder selection' => [
            '... on QueryBuilder_Selection {  }',
            (new InlineFragment('QueryBuilder_Selection'))
                ->setSelectionSet([new QueryBuilder()]),
        ];

        yield 'querybuilder (with alias) selection' => [
            '... on QueryBuilder_Selection { query_alias:  { sub_field } }',
            (new InlineFragment('QueryBuilder_Selection'))->setSelectionSet([
                (new QueryBuilder())
                    ->setAlias('query_alias')
                    ->selectField('sub_field')
            ]),
        ];
    }

    /**
     * @covers \GraphQL\InlineFragment::__construct
     * @covers \GraphQL\InlineFragment::setSelectionSet
     * @covers \GraphQL\InlineFragment::getSelectionSet
     * @covers \GraphQL\InlineFragment::constructSelectionSet
     * @covers \GraphQL\InlineFragment::__toString
     */
    public function testConvertQueryBuilderToString()
    {
        $queryBuilder = new QueryBuilder();

        $fragment = new InlineFragment('Test', $queryBuilder);
        $queryBuilder->selectField('field1');
        $queryBuilder->selectField('field2');

        $this->assertEquals(
            '... on Test { field1 field2 }',
            (string) $fragment
        );
    }
}
