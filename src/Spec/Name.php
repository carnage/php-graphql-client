<?php

declare(strict_types=1);

namespace GraphQL\Spec;

final readonly class Name implements \Stringable
{
    private const GRAMMATICAL_FORM = '/^[_A-Za-z][_0-9A-Za-z]*$/';

    public function __construct(
        private string $name,
    ) {
        if (preg_match(self::GRAMMATICAL_FORM, $name) !== 1) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" does not match allowed grammatical form: "%s"',
                $this->name,
                self::GRAMMATICAL_FORM,
            ));
        }
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
