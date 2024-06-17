<?php

namespace GraphQL\Exception;

use InvalidArgumentException;

/**
 * Class ArgumentException
 *
 * @package GraphQL\Exception
 */
class ArgumentException extends InvalidArgumentException
{
    public function __construct(string $message = '')
    {
        parent::__construct($message);
    }
}
