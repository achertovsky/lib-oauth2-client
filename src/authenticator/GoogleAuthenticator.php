<?php

declare(strict_types=1);

namespace achertovsky\oauth\authenticator;

use RuntimeException;
use Psr\Http\Client\ClientInterface;
use achertovsky\oauth\entity\UserData;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use achertovsky\oauth\exception\OauthException;
use achertovsky\oauth\exception\WrongOauthScopeException;
use achertovsky\oauth\exception\EmailNotVerifiedException;

class GoogleAuthenticator implements AuthenticatorInterface
{
    private const EMAIL = 'email';
    private const EMAIL_VERIFIED = 'email_verified';
    private const NAME = 'name';
    private const PICTURE_URL = 'picture';

    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private string $authenticateUrl,
        private string $clientId,
        private string $clientSecret,
        private string $redirectUrl
    ) {
    }

    /**
     * @todo wrap clientexception
     * @throws ClientExceptionInterface
     * @throws OauthException
     * @throws WrongOauthScopeException
     * @throws EmailNotVerifiedException
     */
    public function authenticate(string $code): UserData
    {
        $payload = $this->fetchPayload($code);
        $this->validatePayload($payload);

        return new UserData(
            $payload[self::EMAIL],
            $payload[self::NAME] ?? null,
            $payload[self::PICTURE_URL] ?? null
        );
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
        $request = $this->requestFactory->createRequest(
            'POST',
            $this->authenticateUrl
        );

        $request = $request->withBody(
            $this->streamFactory->createStream(
                json_encode(
                    [
                        'code' => $code,
                        'client_id' => $this->clientId,
                        'client_secret' => $this->clientSecret,
                        'redirect_uri' => $this->redirectUrl,
                        'grant_type' => 'authorization_code',
                    ]
                )
            )
        );

        return $request;
    }

    private function fetchContent(RequestInterface $request): string
    {
        try {
            $response = $this->client->sendRequest($request);

            return $response->getBody()->getContents();
        } catch (RuntimeException|ClientExceptionInterface $exception) {
            throw new OauthException('Failed to fetch token');
        }
    }
}
