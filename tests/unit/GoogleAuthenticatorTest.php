<?php

declare(strict_types=1);

namespace achertovsky\oauth\tests\unit;

use RuntimeException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\StreamInterface;
use achertovsky\oauth\entity\UserData;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use achertovsky\oauth\exception\OauthException;
use achertovsky\oauth\authenticator\GoogleAuthenticator;
use achertovsky\oauth\exception\WrongOauthScopeException;
use achertovsky\oauth\exception\EmailNotVerifiedException;

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
    private MockObject $requestFactoryMock;
    private MockObject $streamFactoryMock;

    private GoogleAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(ClientInterface::class);
        $this->requestFactoryMock = $this->createMock(RequestFactoryInterface::class);
        $this->streamFactoryMock = $this->createMock(StreamFactoryInterface::class);

        $this->authenticator = new GoogleAuthenticator(
            $this->clientMock,
            $this->requestFactoryMock,
            $this->streamFactoryMock,
            self::URI,
            self::CLIENT_ID,
            self::CLIENT_SECRET,
            self::REDIRECT_URL
        );
    }

    /**
     * @dataProvider dataAuthenticateSuccess
     */
    public function testAuthenticateSuccess(
        UserData $expectedUserData,
        string $payloadFixture
    ): void {
        $streamMock = $this->createMock(StreamInterface::class);
        $this->streamFactoryMock
            ->expects($this->once())
            ->method('createStream')
            ->with(
                json_encode(
                    [
                        'code' => self::AUTH_CODE,
                        'client_id' => self::CLIENT_ID,
                        'client_secret' => self::CLIENT_SECRET,
                        'redirect_uri' => self::REDIRECT_URL,
                        'grant_type' => 'authorization_code',
                    ]
                )
            )
            ->willReturn($streamMock)
        ;

        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock
            ->expects($this->once())
            ->method('withBody')
            ->with($streamMock)
            ->willReturnSelf()
        ;
        $this->requestFactoryMock
            ->expects($this->once())
            ->method('createRequest')
            ->with(
                'POST',
                self::URI
            )
            ->willReturn($requestMock)
        ;

        $streamMock
            ->method('getContents')
            ->willReturn(
                file_get_contents($payloadFixture)
            )
        ;

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

        $this->assertEquals(
            $expectedUserData,
            $this->authenticator->authenticate(
                self::AUTH_CODE
            )
        );
    }

    public static function dataAuthenticateSuccess(): array
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
        $streamMock = $this->createMock(StreamInterface::class);
        $this->streamFactoryMock
            ->expects($this->once())
            ->method('createStream')
            ->with(
                json_encode(
                    [
                        'code' => self::AUTH_CODE,
                        'client_id' => self::CLIENT_ID,
                        'client_secret' => self::CLIENT_SECRET,
                        'redirect_uri' => self::REDIRECT_URL,
                        'grant_type' => 'authorization_code',
                    ]
                )
            )
            ->willReturn($streamMock)
        ;

        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock
            ->expects($this->once())
            ->method('withBody')
            ->with($streamMock)
            ->willReturnSelf()
        ;
        $this->requestFactoryMock
            ->expects($this->once())
            ->method('createRequest')
            ->with(
                'POST',
                self::URI
            )
            ->willReturn($requestMock)
        ;

        $streamMock
            ->method('getContents')
            ->willReturn(
                file_get_contents($fileToRead)
            )
        ;

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

        $this->authenticator->authenticate(self::AUTH_CODE);
    }

    public function testIssueAuthenticateStreamContentsIssue(): void
    {
        $this->expectException(OauthException::class);
        $streamMock = $this->createMock(StreamInterface::class);
        $this->streamFactoryMock
            ->expects($this->once())
            ->method('createStream')
            ->with(
                json_encode(
                    [
                        'code' => self::AUTH_CODE,
                        'client_id' => self::CLIENT_ID,
                        'client_secret' => self::CLIENT_SECRET,
                        'redirect_uri' => self::REDIRECT_URL,
                        'grant_type' => 'authorization_code',
                    ]
                )
            )
            ->willReturn($streamMock)
        ;

        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock
            ->expects($this->once())
            ->method('withBody')
            ->with($streamMock)
            ->willReturnSelf()
        ;
        $this->requestFactoryMock
            ->expects($this->once())
            ->method('createRequest')
            ->with(
                'POST',
                self::URI
            )
            ->willReturn($requestMock)
        ;

        $streamMock
            ->method('getContents')
            ->willThrowException(
                new RuntimeException()
            )
        ;

        $this->authenticator->authenticate(self::AUTH_CODE);
    }

    public function testClientThrowsPsrException(): void
    {
        $this->expectException(OauthException::class);

        $streamMock = $this->createMock(StreamInterface::class);
        $this->streamFactoryMock
            ->expects($this->once())
            ->method('createStream')
            ->with(
                json_encode(
                    [
                        'code' => self::AUTH_CODE,
                        'client_id' => self::CLIENT_ID,
                        'client_secret' => self::CLIENT_SECRET,
                        'redirect_uri' => self::REDIRECT_URL,
                        'grant_type' => 'authorization_code',
                    ]
                )
            )
            ->willReturn($streamMock)
        ;

        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock
            ->expects($this->once())
            ->method('withBody')
            ->with($streamMock)
            ->willReturnSelf()
        ;
        $this->requestFactoryMock
            ->expects($this->once())
            ->method('createRequest')
            ->with(
                'POST',
                self::URI
            )
            ->willReturn($requestMock)
        ;

        $this->clientMock
            ->method('sendRequest')
            ->willThrowException(
                $this->createMock(ClientExceptionInterface::class)
            )
        ;

        $this->authenticator->authenticate(self::AUTH_CODE);
    }
}
