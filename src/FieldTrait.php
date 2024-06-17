<?php

namespace GraphQL;

use GraphQL\Exception\InvalidSelectionException;
use GraphQL\QueryBuilder\QueryBuilderInterface;

trait FieldTrait
{
    /**
     * Stores the selection set desired to get from the query, can include nested queries
     *
     * @var array<string|InlineFragment|Query>
     */
    protected array $selectionSet;

    /**
     * @param array<InlineFragment|Query|QueryBuilderInterface|string> $selectionSet
     * @throws InvalidSelectionException
     */
    public function setSelectionSet(array $selectionSet): self
    {
        $selectionSet = array_map(
            fn ($s) => $s instanceof QueryBuilderInterface ?
                $s->getQuery() :
                $s,
            $selectionSet,
        );


        foreach ($selectionSet as $selection) {
            if (
                !is_string($selection) &&
                !$selection instanceof Query &&
                !$selection instanceof InlineFragment
            ) {
                throw new InvalidSelectionException(sprintf(
                    'Can only set a selection from one of the following: %s',
                    implode(', ', [
                        InlineFragment::class,
                        Query::class,
                        QueryBuilderInterface::class,
                        'string',
                    ]),
                ));
            }
        }

        $this->selectionSet = $selectionSet;
        return $this;
    }

    protected function constructSelectionSet(): string
    {
        if (empty($this->selectionSet)) {
            return '';
        }

        return sprintf(' { %s }', implode(' ', array_map(
            function ($selection) {
                if ($selection instanceof Query) {
                    $selection->setAsNested();
                }
                return $selection;
            },
            $this->selectionSet,
        )));
    }

    /** @return array<string|InlineFragment|Query> */
    public function getSelectionSet(): array
    {
        return $this->selectionSet;
    }
}
