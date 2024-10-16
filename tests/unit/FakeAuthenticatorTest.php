<?php

declare(strict_types=1);

namespace achertovsky\oauth\tests\unit;

use achertovsky\oauth\authenticator\FakeAuthenticator;
use PHPUnit\Framework\TestCase;

class FakeAuthenticatorTest extends TestCase
{
    private FakeAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->authenticator = new FakeAuthenticator();
    }

    public function testAuthenticate(): void
    {
        $userData = $this->authenticator->authenticate('code');
        $this->assertEquals(
            FakeAuthenticator::EMAIL,
            $userData->getEmail()
        );
        $this->assertEquals(
            FakeAuthenticator::NAME,
            $userData->getName()
        );
        $this->assertEquals(
            FakeAuthenticator::AVATAR_URL,
            $userData->getAvatarUrl()
        );
    }
}
