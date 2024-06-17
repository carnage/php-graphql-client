<?php

namespace GraphQL;

use Stringable;

final readonly class RawObject implements Stringable
{
    public function __construct(
        protected string $json
    ) {
    }

    public function __toString(): string
    {
        return $this->json;
    }
}
