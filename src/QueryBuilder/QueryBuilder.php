<?php

namespace GraphQL\QueryBuilder;

use BackedEnum;
use GraphQL\InlineFragment;
use GraphQL\Query;
use Stringable;

class QueryBuilder extends AbstractQueryBuilder
{
    public function selectField(
        InlineFragment|Query|QueryBuilderInterface|string $selectedField,
    ): AbstractQueryBuilder {
        return parent::selectField($selectedField);
    }

    /** @param null|scalar|array<mixed>|BackedEnum|Stringable $value */
    public function setArgument(
        string $name,
        null|bool|float|int|string|array|BackedEnum|Stringable $value,
    ): AbstractQueryBuilder {
        return parent::setArgument($name, $value);
    }

    /** @param null|array<mixed>|scalar|BackedEnum|Stringable $defaultValue */
    public function setVariable(
        string $name,
        string $type,
        bool $isRequired = false,
        null|bool|float|int|string|array|Stringable|BackedEnum $defaultValue = null,
    ): AbstractQueryBuilder {
        return parent::setVariable($name, $type, $isRequired, $defaultValue);
    }
}
