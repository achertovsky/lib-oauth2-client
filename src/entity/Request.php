<?php

declare(strict_types=1);

namespace achertovsky\oauth\entity;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\MessageInterface;

/**
 * @todo actually implement it
 */
class Request implements RequestInterface
{
    public function getRequestTarget(): string
    {

    }

    public function withRequestTarget(string $requestTarget): RequestInterface
    {

    }

    public function getMethod(): string
    {

    }

    public function withMethod(string $method): RequestInterface
    {

    }

    public function getUri(): UriInterface
    {

    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {

    }

    public function getProtocolVersion(): string
    {

    }

    public function withProtocolVersion(string $version): MessageInterface
    {

    }

    public function getHeaders(): array
    {

    }

    public function hasHeader(string $name): bool
    {

    }

    public function getHeader(string $name): array
    {

    }

    public function getHeaderLine(string $name): string
    {

    }

    public function withHeader(string $name, $value): MessageInterface
    {

    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {

    }

    public function withoutHeader(string $name): MessageInterface
    {

    }

    public function getBody(): StreamInterface
    {

    }

    public function withBody(StreamInterface $body): MessageInterface
    {

    }
}
