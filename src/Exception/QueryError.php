<?php

namespace GraphQL\Exception;

use RuntimeException;

/**
 * This exception is triggered when:
 * - the GraphQL endpoint returns an error in the provided query
 */
class QueryError extends RuntimeException
{
    /** @var array<mixed> */
    protected array $errorDetails;

    /** @var array<mixed> */
    protected $data;

    /** @var array<mixed> */
    protected $errors;

    /** @param array<mixed> $errorDetails */
    public function __construct(array $errorDetails)
    {
        $this->errors = $errorDetails['errors'];
        $this->errorDetails = $errorDetails['errors'][0];

        $this->data = [];
        if (!empty($errorDetails['data'])) {
            $this->data = $errorDetails['data'];
        }
        parent::__construct($this->errorDetails['message']);
    }

    /** @return array<mixed> */
    public function getErrorDetails(): array
    {
        return $this->errorDetails;
    }

    /** @return array<mixed> */
    public function getData(): array
    {
        return $this->data;
    }

    /** @return array<mixed> */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
