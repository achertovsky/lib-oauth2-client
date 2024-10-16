<?php

declare(strict_types=1);

namespace achertovsky\oauth\authenticator;

use achertovsky\oauth\entity\UserData;

class FakeAuthenticator implements AuthenticatorInterface
{
    public const EMAIL = 'email@localhost';
    public const NAME = 'John Doe';
    public const AVATAR_URL = 'http://localhost/avatar.jpg';

    public function authenticate(string $code): UserData
    {
        return new UserData(
            self::EMAIL,
            self::NAME,
            self::AVATAR_URL,
        );
    }
}
