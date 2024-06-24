<?php

declare(strict_types=1);

namespace GraphQL\Spec\Selection;

use GraphQL\Spec\Argument;
use GraphQL\Spec\Name;

final readonly class Field implements Selection
{
    /**
     * @param Argument[] $arguments
     * @param Selection[] $selectionSet
     */
    public function __construct(
        private ?Name $alias,
        private Name $name,
        private array $arguments = [],
        private array $selectionSet = [],
    ) {

    }

}
