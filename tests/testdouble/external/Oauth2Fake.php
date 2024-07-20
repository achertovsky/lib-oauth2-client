<?php

declare(strict_types=1);

namespace achertovsky\oauth\tests\testdouble\external;

use achertovsky\oauth\Oauth2;
use achertovsky\oauth\entity\UserData;
use achertovsky\oauth\exception\OauthException;
use achertovsky\oauth\exception\ProviderNotFoundException;

class Oauth2Fake extends Oauth2
{
    public const PROVIDER_DOES_NOT_EXIST = '404provider';
    public const AUTHENTICATION_CODE_FAIL = 'fail';

    public const USER_DATA_EMAIL = 'user@localhost';
    public const USER_DATA_NAME = 'Harold Cartman';
    public const USER_DATA_AVATAR_URL = 'http://localhost/avatar.jpg';

    public function authenticate(string $providerName, string $authenticationCode): UserData
    {
        if ($providerName === self::PROVIDER_DOES_NOT_EXIST) {
            throw new ProviderNotFoundException(self::PROVIDER_DOES_NOT_EXIST);
        }

        if ($authenticationCode === self::AUTHENTICATION_CODE_FAIL) {
            throw new OauthException('Authentication failed');
        }

        return new UserData(
            self::USER_DATA_EMAIL,
            self::USER_DATA_NAME,
            self::USER_DATA_AVATAR_URL
        );
    }
}
