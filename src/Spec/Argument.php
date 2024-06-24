<?php

declare(strict_types=1);

namespace GraphQL\Spec;

final class Argument
{
    public function __construct(
        private Name $name,
        private Value $value,
    ) {
    }

}
