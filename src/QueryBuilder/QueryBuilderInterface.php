<?php

namespace GraphQL\QueryBuilder;

use GraphQL\Query;

interface QueryBuilderInterface
{
    public function getQuery(): Query;
}
