<?php

namespace GraphQL;

use GraphQL\Util\StringLiteralFormatter;
use Stringable;

class Variable implements Stringable
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $required = false,
        public null|string|int|float|bool $defaultValue = null
    ) {
    }

    public function __toString(): string
    {
        $varString = fn (string $suffix = ''): string => sprintf(
            '$%s: %s%s',
            $this->name,
            $this->type,
            $suffix,
        );

        if ($this->required) {
            return $varString('!');
        }

        if (isset($this->defaultValue)) {
            $defaultString = sprintf(
                '=%s',
                StringLiteralFormatter::formatValueForRHS($this->defaultValue)
            );
            return $varString($defaultString);
        }

        return $varString();
    }
}
