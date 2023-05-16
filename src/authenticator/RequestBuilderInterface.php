<?php

declare(strict_types=1);

namespace achertovsky\oauth\authenticator;

use Psr\Http\Message\RequestInterface;

interface RequestBuilderInterface
{
    public function buildRequest(
        string $uri,
        array $body,
        string $method = 'GET'
    ): RequestInterface;
}
