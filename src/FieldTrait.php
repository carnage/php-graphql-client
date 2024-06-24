<?php

namespace GraphQL;

use GraphQL\Exception\InvalidSelectionException;

trait FieldTrait
{
    /**
     * Stores the selection set desired to get from the query, can include nested queries
     *
     * @var array<string|array<string>>
     */
    protected array $selectionSet;

    /**
     * @param array<string|InlineFragment|Query> $selectionSet
     * @throws InvalidSelectionException
     */
    public function setSelectionSet(array $selectionSet)
    {
        $nonStringsFields = array_filter($selectionSet, function ($element) {
            return
                !is_string($element) &&
                !$element instanceof Query &&
                !$element instanceof InlineFragment;
        });
        if (!empty($nonStringsFields)) {
            throw new InvalidSelectionException(
                'One or more of the selection fields provided is not of type string or Query'
            );
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

    public function getSelectionSet()
    {
        return $this->selectionSet;
    }
}
