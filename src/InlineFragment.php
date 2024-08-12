<?php

namespace GraphQL;

use GraphQL\QueryBuilder\QueryBuilderInterface;
use Stringable;

class InlineFragment implements Stringable
{
    use FieldTrait;

    protected const FORMAT = '... on %s%s';

    public function __construct(
        protected string $typeName,
        protected ?QueryBuilderInterface $queryBuilder = null,
    ) {
    }

    public function __toString(): string
    {
        if ($this->queryBuilder !== null) {
            $this->setSelectionSet($this->queryBuilder->getQuery()->getSelectionSet());
        }

        return sprintf(static::FORMAT, $this->typeName, $this->constructSelectionSet());
    }
}
