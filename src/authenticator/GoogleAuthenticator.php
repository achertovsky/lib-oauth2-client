<?php

declare(strict_types=1);

namespace achertovsky\oauth\authenticator;

use achertovsky\oauth\entity\UserData;
use Psr\Http\Client\ClientInterface;
use achertovsky\oauth\entity\Request;

class GoogleAuthenticator implements AuthenticatorInterface
{
    public function __construct(
        private ClientInterface $client
    ) {
    }

    public function authenticate(string $code): UserData
    {
        $request = new Request();
        $this->client->sendRequest($request);

        return new UserData('no');
    }
}
