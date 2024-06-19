<?php

namespace GraphQL\QueryBuilder;

use GraphQL\InlineFragment;
use GraphQL\Query;
use GraphQL\RawObject;
use GraphQL\Variable;

/**
 * Class AbstractQueryBuilder
 *
 * @package GraphQL
 */
abstract class AbstractQueryBuilder implements QueryBuilderInterface
{
    protected Query $query;

    /** @var Variable[] */
    private array $variables;

    /** @var array */
    private array $selectionSet;

    /** @var array */
    private $argumentsList;

    public function __construct(string $queryObject = '', string $alias = '')
    {
        $this->query         = new Query($queryObject, $alias);
        $this->variables     = [];
        $this->selectionSet  = [];
        $this->argumentsList = [];
    }

    /**
     * @param string $alias
     *
     * @return $this
     */
    public function setAlias(string $alias): AbstractQueryBuilder
    {
        $this->query->setAlias($alias);

        return $this;
    }

    /**
     * @return Query
     */
    public function getQuery(): Query
    {
        // Convert nested query builders to query objects
        foreach ($this->selectionSet as $key => $field) {
            if ($field instanceof QueryBuilderInterface) {
                $this->selectionSet[$key] = $field->getQuery();
            }
        }

        $this->query->setVariables($this->variables);
        $this->query->setArguments($this->argumentsList);
        $this->query->setSelectionSet($this->selectionSet);

        return $this->query;
    }

    protected function selectField(
        Query|QueryBuilder|string $selectedField,
    ): AbstractQueryBuilder {
        $this->selectionSet[] = $selectedField;

        return $this;
    }

    /**
     * @param $argumentName
     * @param $argumentValue
     *
     * @return $this
     */
    protected function setArgument(string $argumentName, $argumentValue): AbstractQueryBuilder
    {
        if (is_scalar($argumentValue) || is_array($argumentValue) || $argumentValue instanceof RawObject) {
            $this->argumentsList[$argumentName] = $argumentValue;
        }

        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @param bool   $isRequired
     * @param null   $defaultValue
     *
     * @return $this
     */
    protected function setVariable(
        string $name,
        string $type,
        bool $isRequired = false,
        $defaultValue = null
    ): AbstractQueryBuilder {
        $this->variables[] = new Variable($name, $type, $isRequired, $defaultValue);

        return $this;
    }
}
