<?php

namespace GraphQL;

use GraphQL\Util\StringLiteralFormatter;

final readonly class Variable implements \Stringable
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $nonNullable = false,
        public mixed $defaultValue = null
    ) {
    }

    public function __toString(): string
    {
        $varString = sprintf(
            '$%s: %s%s',
            $this->name,
            $this->type,
            $this->nonNullable ? '!' : '',
        );

        if (!isset($this->defaultValue)) {
            return $varString;
        }

        return sprintf('%s=%s', $varString, is_array($this->defaultValue) ?
            StringLiteralFormatter::formatArrayForGQLQuery($this->defaultValue) :
            StringLiteralFormatter::formatValueForRHS($this->defaultValue));
    }
}
