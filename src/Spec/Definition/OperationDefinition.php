<?php

declare(strict_types=1);

namespace GraphQL\Spec\Definition;

use GraphQL\Spec\Name;
use GraphQL\Spec\OperationType;

final class OperationDefinition
{

    public function __construct(
        private readonly OperationType $operationType,
        private readonly ?Name $name = null,
        private readonly array $variableDefinitions = [],
        private readonly array $directives = [],
        private readonly array $selectionSet = [],
    ) {

    }
}
