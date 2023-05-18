<?php

declare(strict_types=1);

namespace achertovsky\oauth\authenticator;

use Psr\Http\Message\RequestInterface;

interface RequestBuilderInterface
{
    public function buildRequest(
        string $uri,
        string $jsonBody,
        string $method = 'GET'
    ): RequestInterface;
}
