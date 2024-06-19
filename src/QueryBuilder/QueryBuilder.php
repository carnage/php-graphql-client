<?php

namespace GraphQL\QueryBuilder;

use GraphQL\Query;

/**
 * Class QueryBuilder
 *
 * @package GraphQL
 */
class QueryBuilder extends AbstractQueryBuilder
{
    /**
     * Changing method visibility to public
     */
    public function selectField(
        Query|QueryBuilder|string $selectedField
    ): AbstractQueryBuilder {
        return parent::selectField($selectedField);
    }

    /**
     * Changing method visibility to public
     *
     * @param string $argumentName
     * @param        $argumentValue
     *
     * @return $this
     */
    public function setArgument(string $argumentName, $argumentValue): AbstractQueryBuilder
    {
        return parent::setArgument($argumentName, $argumentValue);
    }

    /**
     * Changing method visibility to public
     *
     * @param string $name
     * @param string $type
     * @param bool   $isRequired
     * @param null   $defaultValue
     *
     * @return $this
     */
    public function setVariable(
        string $name,
        string $type,
        bool $isRequired = false,
        $defaultValue = null
    ): AbstractQueryBuilder {
        return parent::setVariable($name, $type, $isRequired, $defaultValue);
    }
}
