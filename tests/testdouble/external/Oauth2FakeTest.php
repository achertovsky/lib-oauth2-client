<?php

declare(strict_types=1);

namespace achertovsky\oauth\tests\testdouble\external;

use PHPUnit\Framework\TestCase;
use achertovsky\oauth\exception\OauthException;
use achertovsky\oauth\exception\ProviderNotFoundException;

class Oauth2FakeTest extends TestCase
{
    public function testThrowsProviderNotFound()
    {
        $oauth2Fake = new Oauth2Fake();

        $this->expectException(ProviderNotFoundException::class);

        $oauth2Fake->authenticate(
            Oauth2Fake::PROVIDER_DOES_NOT_EXIST,
            'valid_code'
        );
    }

    public function testThrowsOauthExceptionFromAuthenticator()
    {
        $oauth2Fake = new Oauth2Fake();

        $this->expectException(OauthException::class);

        $oauth2Fake->authenticate(
            'valid_provider',
            Oauth2Fake::AUTHENTICATION_CODE_FAIL
        );
    }

    public function testSuccess()
    {
        $oauth2Fake = new Oauth2Fake();
        $userData = $oauth2Fake->authenticate(
            'valid_provider',
            'valid_code'
        );

        $this->assertEquals(
            Oauth2Fake::USER_DATA_EMAIL,
            $userData->getEmail()
        );
        $this->assertEquals(
            Oauth2Fake::USER_DATA_NAME,
            $userData->getName()
        );
        $this->assertEquals(
            Oauth2Fake::USER_DATA_AVATAR_URL,
            $userData->getAvatarUrl()
        );
    }
}
