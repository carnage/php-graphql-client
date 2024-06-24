<?php

namespace GraphQL;

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
        return sprintf(
            '$%s: %s%s%s',
            $this->name,
            $this->type,
            $this->nonNullable ? '!' : '',
            isset($this->defaultValue) ?
                sprintf('=%s', json_encode($this->defaultValue)) :
                '',
        );
    }
}
