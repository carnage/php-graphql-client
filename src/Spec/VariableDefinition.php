<?php

declare(strict_types=1);

namespace GraphQL\Spec;

use GraphQL\Spec\TypeReference\Type;

final readonly class VariableDefinition
{
    public function __construct(
        private Name $name,
        private Type $type,
        private ?Value $defaultValue = null,
    ) {
    }
}
