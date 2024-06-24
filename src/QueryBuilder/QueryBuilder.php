<?php

namespace GraphQL\QueryBuilder;

use GraphQL\Query;
use Stringable;

class QueryBuilder extends AbstractQueryBuilder
{
    public function selectField(
        Query|QueryBuilder|string $selectedField
    ): AbstractQueryBuilder {
        return parent::selectField($selectedField);
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
        null|string|int|float|bool $defaultValue = null,
    ): AbstractQueryBuilder {
        return parent::setVariable($name, $type, $isRequired, $defaultValue);
    }
}
