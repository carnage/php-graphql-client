<?php

declare(strict_types=1);

namespace GraphQL\Spec\TypeReference;

final readonly class NonNullType implements Type
{
    public function __construct(
        private NamedType $namedType,
    ) {
    }

    public function __toString(): string
    {
        return sprintf('%s !', $this->namedType);
    }

}
