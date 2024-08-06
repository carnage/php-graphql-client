<?php

namespace GraphQL\Exception;

use InvalidArgumentException;

/**
 * Class InvalidVariableException
 *
 * @package GraphQL\Exception
 */
class InvalidVariableException extends InvalidArgumentException
{
    /**
     * InvalidVariableException constructor.
     */
    public function __construct(string $message = "")
    {
        parent::__construct($message);
    }
}
