<?php

namespace GraphQL\Exception;

use RuntimeException;

/**
 * Class AwsRegionNotSetException
 *
 * @package GraphQL\Exception
 */
class AwsRegionNotSetException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('AWS region not set.');
    }
}
