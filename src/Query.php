<?php

namespace GraphQL;

use GraphQL\Exception\ArgumentException;
use GraphQL\Exception\InvalidVariableException;
use GraphQL\Util\StringLiteralFormatter;
use Stringable;

class Query implements Stringable
{
    use FieldTrait;

    /**
     * The GraphQL query format
     *
     * First string is object name
     * Second string is arguments
     * Third string is selection set
     */
    protected const QUERY_FORMAT = '%s%s%s';

    /** The type of the operation to be executed on the GraphQL server */
    protected const OPERATION_TYPE = 'query';

    /** The name of the operation to be run on the server */
    protected string $operationName = '';

    /**
     * The list of variables to be used in the query
     *
     * @var Variable[]
     */
    protected array $variables;

    /**
     * The list of arguments used when querying data
     *
     * @var array<null|scalar|array<?scalar>|Stringable>
     */
    protected array $arguments = [];

    /**
     * Private member that's not accessible from outside the class,
     * used internally to deduce if query is nested or not
     *
     * @var bool
     */
    protected bool $isNested = false;

    /**
     * GQLQueryBuilder constructor.
     *
     * @param string $fieldName if no value is provided,  empty query object is assumed
     * @param string $alias the alias to use for the query if required
     */
    public function __construct(
        protected string $fieldName = '',
        protected string $alias = ''
    ) {
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function setAlias(string $alias): Query
    {
        $this->alias = $alias;

        return $this;
    }

    public function setOperationName(string $operationName): Query
    {
        if (!empty($operationName)) {
            $this->operationName = " $operationName";
        }

        return $this;
    }

    /** @param Variable[] $variables */
    public function setVariables(array $variables): Query
    {
        foreach ($variables as $variable) {
            if (!$variable instanceof Variable) {
                throw new InvalidVariableException(
                    'All variables must be an instance of GraphQL\\Variable'
                );
            }
        }

        $this->variables = $variables;

        return $this;
    }

    /**
     * @param array<null|scalar|array<?scalar>|Stringable> $arguments
     * @throws ArgumentException for invalid arguments
     */
    public function setArguments(array $arguments): Query
    {
        foreach ($arguments as $name => $argument) {
            if (!is_string($name)) {
                throw new ArgumentException(
                    'All query arguments require string keys,' .
                    'these represent the argument name'
                );
            }

            if (is_array($argument)) {
                foreach ($argument as $item) {
                    if (!is_null($item) && !is_scalar($item)) {
                        throw new ArgumentException(
                            'Only arrays with null|scalar items can be handled',
                        );
                    }
                }
            }

            if (is_object($argument) && !method_exists($argument, '__toString')) {
                throw new ArgumentException(
                    'Only objects with the __toString() method can be handled',
                );
            }
        }

        $this->arguments = $arguments;

        return $this;
    }

    /** @return array<null|scalar|array<?scalar>|Stringable> */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    protected function constructVariables(): string
    {
        if (empty($this->variables)) {
            return '';
        }

        return sprintf('( %s )', implode(' ', $this->variables));
    }

    protected function constructArguments(): string
    {
        if (empty($this->arguments)) {
            return '';
        }

        $formattedArguments = [];
        foreach ($this->arguments as $name => $argument) {
            if (is_scalar($argument) || is_null($argument)) {
                $formattedArgument = StringLiteralFormatter::formatValueForRHS($argument);
            } elseif (is_array($argument)) {
                $formattedArgument = StringLiteralFormatter::formatArrayForGQLQuery($argument);
            } else {
                $formattedArgument = (string) $argument;
            }

            $formattedArguments[] = sprintf('%s: %s', $name, $formattedArgument);
        }

        return sprintf('(%s)', implode(' ', $formattedArguments));
    }

    protected function generateFieldName(): string
    {
        return empty($this->alias) ? $this->fieldName : sprintf('%s: %s', $this->alias, $this->fieldName);
    }

    protected function generateSignature(): string
    {
        $signatureFormat = '%s%s%s';

        return sprintf($signatureFormat, static::OPERATION_TYPE, $this->operationName, $this->constructVariables());
    }

    public function setAsNested(): void
    {
        $this->isNested = true;
    }

    public function __toString(): string
    {
        $queryFormat = self::QUERY_FORMAT;
        $selectionSetString = $this->constructSelectionSet();

        if (!$this->isNested) {
            $queryFormat = $this->generateSignature();
            if ($this->fieldName === '') {
                return $queryFormat . $selectionSetString;
            } else {
                $queryFormat = $this->generateSignature() . ' { ' . static::QUERY_FORMAT . ' }';
            }
        }
        $argumentsString = $this->constructArguments();

        return sprintf($queryFormat, $this->generateFieldName(), $argumentsString, $selectionSetString);
    }
}
