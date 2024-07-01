<?php

namespace GraphQL\QueryBuilder;

use GraphQL\InlineFragment;
use GraphQL\Query;
use GraphQL\RawObject;

class QueryBuilder extends AbstractQueryBuilder
{
    public function selectField(
        InlineFragment | Query | QueryBuilderInterface | string $selection
    ): AbstractQueryBuilder {
        return parent::selectField($selection);
    }

    /** @param null|scalar|array<?scalar>|RawObject $argumentValue */
    public function setArgument(
        string $argumentName,
        null|bool|float|int|string|array|RawObject $argumentValue,
    ): AbstractQueryBuilder {
        return parent::setArgument($argumentName, $argumentValue);
    }

    /** @param null|scalar|array<?scalar>|RawObject $defaultValue */
    public function setVariable(
        string $name,
        string $type,
        bool $isRequired = false,
        null|bool|float|int|string|array|RawObject $defaultValue = null,
    ): AbstractQueryBuilder {
        return parent::setVariable($name, $type, $isRequired, $defaultValue);
    }
}
