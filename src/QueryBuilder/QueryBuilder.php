<?php

namespace GraphQL\QueryBuilder;

use GraphQL\InlineFragment;
use GraphQL\Query;
use Stringable;

class QueryBuilder extends AbstractQueryBuilder
{
    public function selectField(
        InlineFragment | Query | QueryBuilderInterface | string $selection
    ): AbstractQueryBuilder {
        return parent::selectField($selection);
    }

    /** @param null|scalar|array<?scalar>|Stringable $argumentValue */
    public function setArgument(
        string $argumentName,
        null|bool|float|int|string|array|Stringable $argumentValue,
    ): AbstractQueryBuilder {
        return parent::setArgument($argumentName, $argumentValue);
    }

    public function setVariable(
        string $name,
        string $type,
        bool $isRequired = false,
        mixed $defaultValue = null,
    ): AbstractQueryBuilder {
        return parent::setVariable($name, $type, $isRequired, $defaultValue);
    }
}
