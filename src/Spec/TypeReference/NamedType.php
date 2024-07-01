<?php

declare(strict_types=1);

namespace GraphQL\Spec\TypeReference;

use GraphQL\Spec\Name;

final readonly class NamedType implements Type
{
    public function __construct(
        private Name $name,
    ) {
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
