<?php

namespace GraphQL;

use Stringable;

class RawObject implements Stringable
{
    public function __construct(
        protected string $objectString
    ) {
    }

    public function __toString(): string
    {
        return $this->objectString;
    }
}
