<?php

namespace GraphQL\Exception;

use InvalidArgumentException;

/**
 * Class InvalidSelectionException
 *
 * @package GraphQL\Exception
 */
class InvalidSelectionException extends InvalidArgumentException
{
    public function __construct(string $message = '')
    {
        parent::__construct($message);
    }
}
