<?php

declare(strict_types=1);

namespace achertovsky\oauth\tests\unit;

use achertovsky\oauth\Oauth2;
use PHPUnit\Framework\TestCase;
use achertovsky\oauth\entity\UserData;
use PHPUnit\Framework\MockObject\MockObject;
use achertovsky\oauth\exception\ProviderNotFoundException;
use achertovsky\oauth\authenticator\AuthenticatorInterface;

class Oauth2Test extends TestCase
{
    private const PROVIDER_NAME = 'name';
    private const AUTH_CODE = 'code';
    private const EMAIL = 'email@gmail.com';

    private MockObject $authenticatorMock;

    private Oauth2 $oauth;

    private UserData $expectedUserData;

    protected function setUp(): void
    {
        $this->oauth = new Oauth2();

        $this->authenticatorMock = $this->createMock(AuthenticatorInterface::class);

        $this->oauth->addAuthenticator(
            self::PROVIDER_NAME,
            $this->authenticatorMock
        );

        $this->expectedUserData = new UserData(
            self::EMAIL
        );
    }

    public function testAuthenticate(): void
    {
        $this->authenticatorMock
            ->expects($this->once())
            ->method('authenticate')
            ->with(self::AUTH_CODE)
            ->willReturn($this->expectedUserData)
        ;
        $this->assertEquals(
            $this->expectedUserData,
            $this->oauth->authenticate(
                self::PROVIDER_NAME,
                self::AUTH_CODE
            )
        );
    }

    public function testAuthenticateWrongProvider(): void
    {
        $this->expectException(ProviderNotFoundException::class);

        $this->oauth->authenticate(
            'not existing provider',
            self::AUTH_CODE
        );
    }
}
