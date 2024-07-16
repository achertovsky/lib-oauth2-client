<?php

declare(strict_types=1);

namespace achertovsky\oauth\authenticator;

use achertovsky\oauth\entity\UserData;
use achertovsky\oauth\exception\OauthException;

interface AuthenticatorInterface
{
    /**
     * @throws OauthException
     */
    public function authenticate(string $code): UserData;
}
