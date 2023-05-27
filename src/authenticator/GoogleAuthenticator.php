<?php

declare(strict_types=1);

namespace achertovsky\oauth\authenticator;

use RuntimeException;
use Psr\Http\Client\ClientInterface;
use achertovsky\oauth\entity\UserData;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Client\ClientExceptionInterface;
use achertovsky\oauth\exception\OauthException;
use achertovsky\oauth\exception\WrongOauthScopeException;
use achertovsky\oauth\exception\EmailNotVerifiedException;

class GoogleAuthenticator implements AuthenticatorInterface
{
    private const EMAIL = 'email';
    private const EMAIL_VERIFIED = 'email_verified';

    public function __construct(
        private ClientInterface $client,
        private RequestBuilderInterface $requestBuilder,
        private string $authenticateUrl,
        private string $clientId,
        private string $clientSecret,
        private string $redirectUrl
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws OauthException
     * @throws WrongOauthScopeException
     * @throws EmailNotVerifiedException
     */
    public function authenticate(string $code): UserData
    {
        $payload = $this->fetchPayload($code);
        $this->validatePayload($payload);

        return new UserData($payload[self::EMAIL]);
    }

    private function fetchPayload(string $code): array
    {
        $jwt = $this->fetchIdTokenData($code);

        $jwtParts = explode('.', $jwt);
        if (!array_key_exists(1, $jwtParts)) {
            throw new OauthException('Invalid jwt');
        }

        $payload = json_decode(base64_decode($jwtParts[1]), true);
        if (!is_array($payload)) {
            throw new OauthException('Invalid payload');
        }

        return $payload;
    }

    private function validatePayload(array $payload): void
    {
        if (!array_key_exists(self::EMAIL, $payload)) {
            throw new WrongOauthScopeException('Missing email scope');
        }

        if (
            !array_key_exists(self::EMAIL_VERIFIED, $payload)
            || $payload[self::EMAIL_VERIFIED] !== true
        ) {
            throw new EmailNotVerifiedException('Unverified email received');
        }
    }

    private function fetchIdTokenData(string $code): string
    {
        $stringContent = $this->fetchContent(
            $this->prepareRequest($code)
        );
        $tokenData = json_decode(
            $stringContent,
            true
        );
        if ($tokenData === null) {
            throw new OauthException(
                sprintf(
                    'Not a json data: %s',
                    $stringContent
                )
            );
        }

        if (!array_key_exists('id_token', $tokenData)) {
            throw new WrongOauthScopeException(
                sprintf(
                    'Expected payload not received: %s',
                    $stringContent
                )
            );
        }

        return $tokenData['id_token'];
    }

    private function prepareRequest(string $code): RequestInterface
    {
        return $this->requestBuilder->buildRequest(
            $this->authenticateUrl,
            json_encode(
                [
                    'grant_type' => 'authorization_code',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri' => $this->redirectUrl,
                    'code' => $code,
                ]
            ),
            'POST'
        );
    }

    private function fetchContent(RequestInterface $request): string
    {
        $response = $this->client->sendRequest($request);
        try {
            return $response->getBody()->getContents();
        } catch (RuntimeException $exception) {
            throw new OauthException('Failed to fetch token');
        }
    }
}
