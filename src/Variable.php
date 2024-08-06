<?php

namespace GraphQL;

use GraphQL\Util\StringLiteralFormatter;

final readonly class Variable implements \Stringable
{
    /** @param null|scalar|array<mixed>|RawObject $defaultValue */
    public function __construct(
        public string $name,
        public string $type,
        public bool $nonNullable = false,
        public null|bool|float|int|string|array|RawObject $defaultValue = null,
    ) {
    }

    /**
     * @TODO make the default value conversion more robust.
     * json_encode would not handle Enum types which should not have quotes around it
     */
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
