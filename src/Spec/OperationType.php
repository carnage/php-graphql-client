<?php

declare(strict_types=1);

namespace GraphQL\Spec;

enum OperationType: string
{
    /** a read only fetch */
    case Query = 'query';

    /** a write followed by a fetch */
    case Mutation = 'mutation';

    /** a long-lived request that fetches data in response to source events */
    case Subscription = 'subscription';
}
