<?php

namespace GraphQL\Auth;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

interface AuthInterface
{
    /** @param array<mixed> $options */
    public function run(Request $request, array $options = []): RequestInterface;
}
