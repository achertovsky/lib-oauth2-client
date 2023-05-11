<?php

declare(strict_types=1);

namespace achertovsky\oauth;

use achertovsky\oauth\entity\UserData;
use achertovsky\oauth\exception\ProviderNotFoundException;
use achertovsky\oauth\authenticator\AuthenticatorInterface;

class Oauth2
{
    public function __construct(
        private array $authenticators = []
    ) {
    }

    public function addAuthenticator(
        string $providerName,
        AuthenticatorInterface $authenticator
    ): self {
        $this->authenticators[$providerName] = $authenticator;

        return $this;
    }

    public function authenticate(
        string $providerName,
        string $authenticationCode
    ): UserData {
        if (!key_exists($providerName, $this->authenticators)) {
            throw new ProviderNotFoundException();
        }

        return $this->authenticators[$providerName]
            ->authenticate($authenticationCode)
        ;
    }
}
