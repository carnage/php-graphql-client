<?php

namespace GraphQL\Exception;

use RuntimeException;

/**
 * This exception is triggered when:
 * - the GraphQL endpoint returns an error in the provided query
 */
class QueryError extends RuntimeException
{
    /** @var array */
    protected array $errorDetails;

    /** @var array */
    protected $data;

    /** @var array */
    protected $errors;

    /** @param array $errorDetails */
    public function __construct(array $errorDetails)
    {
        $this->errorDetails = $errorDetails['errors'][0];
        $this->data = [];
        if (!empty($errorDetails['data'])) {
            $this->data = $errorDetails['data'];
        }
        $this->errors = $errorDetails['errors'];
        parent::__construct($this->errorDetails['message']);
    }

    /**
     * @return array
     */
    public function getErrorDetails(): array
    {
        return $this->errorDetails;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
