<?php

declare(strict_types=1);

namespace GraphQL\Spec\TypeReference;

final readonly class ListType implements Type
{
    public function __construct(
        private Type $type
    ) {
    }

    public function __toString(): string
    {
        return sprintf('[ %s ]', $this->type);
    }
}
