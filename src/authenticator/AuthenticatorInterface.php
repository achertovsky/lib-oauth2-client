<?php

declare(strict_types=1);

namespace achertovsky\oauth\authenticator;

use achertovsky\oauth\entity\UserData;

interface AuthenticatorInterface
{
    public function authenticate(string $code): UserData;
}
