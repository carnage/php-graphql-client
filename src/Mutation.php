<?php

namespace GraphQL;

class Mutation extends Query
{
    /**
     * Stores the name of the type of the operation to be executed on the GraphQL server
     */
    protected const OPERATION_TYPE = OperationType::Mutation->value;
}
