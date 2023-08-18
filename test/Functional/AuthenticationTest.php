<?php

declare(strict_types=1);

namespace ApiTest\Functional;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function json_decode;

class AuthenticationTest extends AbstractFunctionalTest
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAuthenticateInvalidUser(): void
    {
        $this->authenticateInvalidIdentity($this->getInvalidFrontendAccessTokenCredentials());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAuthenticateInvalidAdmin(): void
    {
        $this->authenticateInvalidIdentity($this->getInvalidAdminAccessTokenCredentials());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAuthenticateAdmin(): void
    {
        $this->createAdmin();

        $response = $this->post('/security/generate-token', $this->getValidAdminAccessTokenCredentials());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseOk($response);
        $this->assertArrayHasKey('token_type', $data);
        $this->assertArrayHasKey('expires_in', $data);
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertNotEmpty($data['expires_in']);
        $this->assertNotEmpty($data['access_token']);
        $this->assertNotEmpty($data['refresh_token']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAuthenticateUser(): void
    {
        $this->createUser();

        $response = $this->post('/security/generate-token', $this->getValidFrontendAccessTokenCredentials());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseOk($response);
        $this->assertArrayHasKey('token_type', $data);
        $this->assertArrayHasKey('expires_in', $data);
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertSame('Bearer', $data['token_type']);
        $this->assertNotEmpty($data['expires_in']);
        $this->assertNotEmpty($data['access_token']);
        $this->assertNotEmpty($data['refresh_token']);
    }

    public function testInvalidRefreshToken(): void
    {
        $response = $this->post('/security/refresh-token', $this->getInvalidFrontendRefreshTokenCredentials());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseUnauthorized($response);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('error_description', $data);
        $this->assertArrayHasKey('hint', $data);
        $this->assertArrayHasKey('message', $data);

        $this->assertSame('invalid_request', $data['error']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testRefreshToken(): void
    {
        $user = $this->createUser();

        $this->loginAs($user->getIdentity(), self::DEFAULT_PASSWORD);

        $response = $this->post('/security/refresh-token', $this->getValidFrontendRefreshTokenCredentials());
        $this->assertResponseOk($response);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('token_type', $data);
        $this->assertArrayHasKey('expires_in', $data);
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertSame('Bearer', $data['token_type']);
        $this->assertNotEquals($this->getAccessToken(), $data['access_token']);
        $this->assertNotEquals($this->getRefreshToken(), $data['refresh_token']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAdminCannotAuthenticateAsUser(): void
    {
        $admin         = $this->createAdmin();
        $errorMessages = $this->getContainer()->get('config')['authentication']['invalid_credentials'];

        $response = $this->post('/security/generate-token', $this->getValidFrontendAccessTokenCredentials([
            'username' => $admin->getIdentity(),
        ]));

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseBadRequest($response);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('error_description', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame($errorMessages['error'], $data['error']);
        $this->assertSame($errorMessages['message'], $data['message']);
        $this->assertSame($errorMessages['error_description'], $data['error_description']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUserCannotAuthenticateAsAdmin(): void
    {
        $errorMessages = $this->getContainer()->get('config')['authentication']['invalid_credentials'];

        $user     = $this->createUser();
        $response = $this->post('/security/generate-token', $this->getValidAdminAccessTokenCredentials([
            'username' => $user->getIdentity(),
        ]));

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseBadRequest($response);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('error_description', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame($errorMessages['error'], $data['error']);
        $this->assertSame($errorMessages['message'], $data['message']);
        $this->assertSame($errorMessages['error_description'], $data['error_description']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function authenticateInvalidIdentity(array $credentials): void
    {
        $errorMessages = $this->getContainer()->get('config')['authentication']['invalid_credentials'];

        $response = $this->post('/security/generate-token', $credentials);
        $this->assertResponseBadRequest($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('error_description', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame($errorMessages['error'], $data['error']);
        $this->assertSame($errorMessages['message'], $data['message']);
        $this->assertSame($errorMessages['error_description'], $data['error_description']);
    }
}
