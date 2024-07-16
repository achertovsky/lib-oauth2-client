<?php

declare(strict_types=1);

namespace achertovsky\tests\unit;

use RuntimeException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\StreamInterface;
use achertovsky\oauth\entity\UserData;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\MockObject\MockObject;
use achertovsky\oauth\exception\OauthException;
use achertovsky\oauth\authenticator\GoogleAuthenticator;
use achertovsky\oauth\exception\WrongOauthScopeException;
use achertovsky\oauth\exception\EmailNotVerifiedException;
use achertovsky\oauth\authenticator\RequestBuilderInterface;
use Psr\Http\Client\ClientExceptionInterface;

class GoogleAuthenticatorTest extends TestCase
{
    private const AUTH_CODE = 'code';
    private const URI = 'http://oauth-google.com/';
    private const EXPECTED_EMAIL = 'email@gmail.com';
    private const CLIENT_ID = 'appid';
    private const CLIENT_SECRET = 'appsecret';
    private const REDIRECT_URL = 'http://baseurl.com';
    private const NAME = 'John Doe';
    private const PICTURE_URL = 'http://picture.com';

    private MockObject $clientMock;
    private MockObject $builderMock;

    private GoogleAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(ClientInterface::class);
        $this->builderMock = $this->createMock(RequestBuilderInterface::class);

        $this->authenticator = new GoogleAuthenticator(
            $this->clientMock,
            $this->builderMock,
            self::URI,
            self::CLIENT_ID,
            self::CLIENT_SECRET,
            self::REDIRECT_URL
        );
    }

    /**
     * @dataProvider dataAuthenticate
     */
    public function testAuthenticate(
        UserData $expectedUserData,
        string $payloadFixture
    ): void {
        $this->configureMocks($payloadFixture);
        $this->assertEquals(
            $expectedUserData,
            $this->authenticator->authenticate(
                self::AUTH_CODE
            )
        );
    }

    public static function dataAuthenticate(): array
    {
        return [
            'response for userInfo.email scope' => [
                new UserData(
                    self::EXPECTED_EMAIL
                ),
                __DIR__ . '/../fixture/google_response.json'
            ],
            'user profile with name and picture' => [
                new UserData(
                    self::EXPECTED_EMAIL,
                    self::NAME,
                    self::PICTURE_URL
                ),
                __DIR__ . '/../fixture/google_response_with_name_and_avatar_url.json'
            ]
        ];
    }

    public static function dataIssueAuthenticate(): array
    {
        return [
            'id_token is not jwt' => [
                __DIR__ . '/../fixture/google_response_id_token_not_jwt.json',
                OauthException::class,
            ],
            'id_token missing' => [
                __DIR__ . '/../fixture/google_response_id_token_not_set.json',
                WrongOauthScopeException::class,
            ],
            'no email' => [
                __DIR__ . '/../fixture/google_response_no_email.json',
                WrongOauthScopeException::class,
            ],
            'email not verified' => [
                __DIR__ . '/../fixture/google_response_unverified_email.json',
                EmailNotVerifiedException::class,
            ],
            'response not json' => [
                __DIR__ . '/../fixture/google_response_not_json',
                OauthException::class,
            ],
            'jwt malformed' => [
                __DIR__ . '/../fixture/google_response_id_token_jwt_malformed.json',
                OauthException::class,
            ],
        ];
    }

    /**
     * @dataProvider dataIssueAuthenticate
     */
    public function testIssueAuthenticate(
        string $fileToRead,
        string $expectedException
    ): void {
        $this->expectException($expectedException);
        $this->configureMocks($fileToRead);
        $this->authenticator->authenticate(self::AUTH_CODE);
    }

    public function testIssueAuthenticateStreamContentsIssue(): void
    {
        $this->expectException(OauthException::class);
        $this->configureMocks();
        $this->authenticator->authenticate(self::AUTH_CODE);
    }

    private function configureMocks(
        ?string $fileName = null
    ): void {
        $requestMock = $this->createMock(RequestInterface::class);

        $this->builderMock
            ->expects($this->once())
            ->method('buildRequest')
            ->with(
                self::URI,
                json_encode(
                    [
                        'grant_type' => 'authorization_code',
                        'client_id' => self::CLIENT_ID,
                        'client_secret' => self::CLIENT_SECRET,
                        'redirect_uri' => self::REDIRECT_URL,
                        'code' => self::AUTH_CODE
                    ]
                )
            )
            ->willReturn($requestMock)
        ;

        $streamMock = $this->createMock(StreamInterface::class);
        if ($fileName !== null) {
            $streamMock
                ->method('getContents')
                ->willReturn(
                    file_get_contents($fileName)
                )
            ;
        } else {
            $streamMock
                ->method('getContents')
                ->willThrowException(
                    new RuntimeException()
                )
            ;
        }

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
            ->method('getBody')
            ->willReturn($streamMock)
        ;

        $this->clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with($requestMock)
            ->willReturn($responseMock)
        ;
    }

    public function testClientThrowsPsrException(): void
    {
        $this->expectException(OauthException::class);

        $this->clientMock
            ->method('sendRequest')
            ->willThrowException(
                $this->createMock(ClientExceptionInterface::class)
            )
        ;

        $this->authenticator->authenticate(self::AUTH_CODE);
    }
}
