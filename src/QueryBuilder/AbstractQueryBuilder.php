<?php

namespace GraphQL\QueryBuilder;

use BackedEnum;
use GraphQL\InlineFragment;
use GraphQL\Query;
use GraphQL\RawObject;
use GraphQL\Variable;
use Stringable;

abstract class AbstractQueryBuilder implements QueryBuilderInterface
{
    protected Query $query;

    /** @var Variable[] */
    private array $variables = [];

    /** @var array<string|InlineFragment|Query|QueryBuilderInterface> */
    private array $selectionSet = [];

    /** @var array<null|scalar|array<mixed>|Stringable|BackedEnum> */
    private array $argumentsList = [];

    public function __construct(string $queryObject = '', string $alias = '')
    {
        $this->query = new Query($queryObject, $alias);
    }

    public function setAlias(string $alias): self
    {
        $this->query->setAlias($alias);

        return $this;
    }

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
        InlineFragment|Query|QueryBuilderInterface|string $selectedField,
    ): AbstractQueryBuilder {
        $this->selectionSet[] = $selectedField;

        return $this;
    }

    /**
     * @param null|array<mixed>|scalar|BackedEnum|Stringable $value
     */
    protected function setArgument(
        string $name,
        null|bool|float|int|string|array|Stringable|BackedEnum $value
    ): self {
        if (!is_null($value)) {
            $this->argumentsList[$name] = $value;
        }

        return $this;
    }

    /** @param null|array<mixed>|scalar|BackedEnum|Stringable $defaultValue */
    protected function setVariable(
        string $name,
        string $type,
        bool $isRequired = false,
        null|bool|float|int|string|array|Stringable|BackedEnum $defaultValue = null,
    ): AbstractQueryBuilder {
        $this->variables[] = new Variable(
            $name,
            $type,
            $isRequired,
            $defaultValue
        );

        return $this;
    }
}
