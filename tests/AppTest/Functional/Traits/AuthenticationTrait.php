<?php

namespace AppTest\Functional\Traits;

use AppTest\Functional\Exception\AuthenticationException;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\ServerRequest;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * Trait AuthenticationTrait
 * @package AppTest\Functional\Traits
 */
trait AuthenticationTrait
{
    private string $tokenType = 'Bearer';

    private ?string $accessToken = null;

    private ?string $refreshToken = null;

    private function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    private function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    private function setTokenType(string $tokenType = 'Bearer'): self
    {
        $this->tokenType = $tokenType;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    public function isAuthenticated(): bool
    {
        return ! is_null($this->accessToken);
    }

    public function loginAs
    (
        string $identity,
        string $password,
        string $clientId = 'frontend',
        string $clientSecret = 'frontend',
        string $scope = 'api'
    ): void
    {
        $request = $this->createLoginRequest([
            'grant_type' => 'password',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => $scope,
            'username' => $identity,
            'password' => $password,
        ]);

        $authorizationServer = $this->getContainer()->get(AuthorizationServer::class);
        $responseFactory = $this->getContainer()->get(ResponseFactoryInterface::class);
        $response = $responseFactory->createResponse();
        try {
            $response = $authorizationServer->respondToAccessTokenRequest($request, $responseFactory->createResponse());
        } catch (OAuthServerException $exception) {
            $response = $exception->generateHttpResponse($response);
        }

        $response->getBody()->rewind();
        if (StatusCodeInterface::STATUS_OK !== $response->getStatusCode()) {
            throw AuthenticationException::fromResponse($response);
        }

        $body = json_decode($response->getBody()->getContents(),true);

        $this
            ->setTokenType($body['token_type'])
            ->setAccessToken($body['access_token'])
            ->setRefreshToken($body['refresh_token']);
    }

    private function createLoginRequest(array $bodyParams): ServerRequest
    {
        return new ServerRequest(
            [],
            [],
            '',
            RequestMethodInterface::METHOD_POST,
            'php://input',
            [],
            [],
            [],
            $bodyParams,
            '1.1',
        );
    }

    public function getAuthorizationHeader(): array
    {
        return [
            'Authorization' => sprintf('%s %s', $this->getTokenType(), $this->getAccessToken())
        ];
    }
}
