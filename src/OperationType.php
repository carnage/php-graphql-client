<?php

declare(strict_types=1);

namespace GraphQL;

enum OperationType: string
{
    case Query = 'query';
    case Mutation = 'mutation';
    case Subscription = 'subscription';
}
