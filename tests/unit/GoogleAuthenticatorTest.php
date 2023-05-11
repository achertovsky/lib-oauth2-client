<?php

declare(strict_types=1);

namespace achertovsky\tests\unit;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use PHPUnit\Framework\TestCase;
use achertovsky\oauth\authenticator\GoogleAuthenticator;
use achertovsky\oauth\entity\Request;

class GoogleAuthenticatorTest extends TestCase
{
    private const AUTH_CODE = 'code';

    private MockObject $client;

    private GoogleAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->authenticator = new GoogleAuthenticator($this->client);
    }

    public function testClientIsCalled(): void
    {
        $this->client
            ->expects($this->once())
            ->method('sendRequest')
        ;

        $this->authenticator->authenticate(
            self::AUTH_CODE
        );
    }
}
