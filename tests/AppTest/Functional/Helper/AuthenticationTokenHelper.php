<?php

namespace AppTest\Functional\Helper;

/**
 * Class AuthenticationTokenHelper
 * @package AppTest\Helper
 */
class AuthenticationTokenHelper
{
    private string $accessToken;

    private string $refreshToken;

    private string $tokenType;

    public function __construct(string $accessToken, string $refreshToken, string $tokenType = 'Bearer')
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->tokenType = $tokenType;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    public function getAuthorizationHeader(): array
    {
        return [
            'Authorization' => sprintf('%s %s', $this->getTokenType(), $this->getAccessToken())
        ];
    }
}
