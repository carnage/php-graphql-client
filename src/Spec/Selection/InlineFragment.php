<?php

declare(strict_types=1);

namespace GraphQL\Spec\Selection;

use GraphQL\Spec\TypeReference\NamedType;

final readonly class InlineFragment implements Selection
{
    /**
     * @param Selection[] $selectionSet
     */
    public function __construct(
        private ?NamedType $typeCondition,
        private array $selectionSet = [],
    ) {
    }

}
