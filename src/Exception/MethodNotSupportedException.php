<?php

namespace GraphQL\Exception;

use RuntimeException;

/**
 * Class MethodNotSupportedException
 *
 * @package GraphQL\Exception
 */
class MethodNotSupportedException extends RuntimeException
{
    public function __construct(string $requestMethod)
    {
        parent::__construct("Method \"$requestMethod\" is currently unsupported by client.");
    }
}
