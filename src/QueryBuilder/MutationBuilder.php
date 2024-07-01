<?php

namespace GraphQL\QueryBuilder;

use GraphQL\Mutation;

class MutationBuilder extends QueryBuilder
{
    public function __construct(string $queryObject = '', string $alias = '')
    {
        parent::__construct($queryObject, $alias);
        $this->query = new Mutation($queryObject, $alias);
    }

    public function getMutation(): Mutation
    {
        assert($this->getQuery() instanceof Mutation);
        return $this->getQuery();
    }
}
