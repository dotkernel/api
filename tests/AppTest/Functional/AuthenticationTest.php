<?php

namespace AppTest\Functional;

use AppTest\Functional\Traits\DatabaseTrait;
use Fig\Http\Message\StatusCodeInterface;


/**
 * Class FunctionalTest
 * @package AppTest\Functional
 */
class AuthenticationTest extends AbstractFunctionalTest
{
    use DatabaseTrait;

    public function testAuthenticateInvalidUser()
    {
        $errorMessages = $this->getContainer()->get('config')['authentication']['invalid_credentials'];
        $response = $this->post('/security/generate-token', [
            'username' => 'invalid@test.com',
            'password' => '12345678',
            'grant_type' => 'password',
            'client_id' => 'frontend',
            'client_secret' => 'frontend',
            'scope' => 'api',
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame(StatusCodeInterface::STATUS_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('error_description', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame($errorMessages['error'], $data['error']);
        $this->assertSame($errorMessages['message'], $data['message']);
        $this->assertSame($errorMessages['error_description'], $data['error_description']);
    }

    public function testAuthenticateInvalidAdmin()
    {
        $errorMessages = $this->getContainer()->get('config')['authentication']['invalid_credentials'];
        $response = $this->post('/security/generate-token', [
            'username' => 'admin@invalid.com',
            'password' => '12345678',
            'grant_type' => 'password',
            'client_id' => 'admin',
            'client_secret' => 'admin',
            'scope' => 'api',
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame(StatusCodeInterface::STATUS_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('error_description', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame($errorMessages['error'], $data['error']);
        $this->assertSame($errorMessages['message'], $data['message']);
        $this->assertSame($errorMessages['error_description'], $data['error_description']);
    }

    public function testAuthenticateAdmin()
    {
        $response = $this->post('/security/generate-token', [
            'username' => 'admin',
            'password' => 'dotadmin',
            'grant_type' => 'password',
            'client_id' => 'admin',
            'client_secret' => 'admin',
            'scope' => 'api',
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $this->assertArrayHasKey('token_type', $data);
        $this->assertArrayHasKey('expires_in', $data);
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertSame('Bearer', $data['token_type']);
        $this->assertNotEmpty($data['expires_in']);
        $this->assertNotEmpty($data['access_token']);
        $this->assertNotEmpty($data['refresh_token']);
    }

    public function testAuthenticateUser()
    {
        $response = $this->post('/security/generate-token', [
            'username' => 'test@dotkernel.com',
            'password' => 'dotkernel',
            'grant_type' => 'password',
            'client_id' => 'frontend',
            'client_secret' => 'frontend',
            'scope' => 'api',
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $this->assertArrayHasKey('token_type', $data);
        $this->assertArrayHasKey('expires_in', $data);
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertSame('Bearer', $data['token_type']);
        $this->assertNotEmpty($data['expires_in']);
        $this->assertNotEmpty($data['access_token']);
        $this->assertNotEmpty($data['refresh_token']);
    }

    public function testInvalidRefreshToken()
    {
        $response = $this->post('/security/refresh-token', [
            'grant_type' => 'refresh_token',
            'client_id' => 'frontend',
            'client_secret' => 'frontend',
            'scope' => 'api',
            'refresh_token' => 'invalid_token',
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('error_description', $data);
        $this->assertArrayHasKey('hint', $data);
        $this->assertArrayHasKey('message', $data);

        $this->assertSame('invalid_request', $data['error']);
    }

    public function testRefreshToken()
    {
        $this->loginAs('test@dotkernel.com', 'dotkernel');
        $authTokens = $this->authTokens;

        $response = $this->post('/security/refresh-token', [
            'grant_type' => 'refresh_token',
            'client_id' => 'frontend',
            'client_secret' => 'frontend',
            'scope' => 'api',
            'refresh_token' => $authTokens['refresh_token']
        ]);

        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('token_type', $data);
        $this->assertArrayHasKey('expires_in', $data);
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertSame('Bearer', $data['token_type']);
        $this->assertNotEquals($authTokens['access_token'], $data['access_token']);
        $this->assertNotEquals($authTokens['refresh_token'], $data['refresh_token']);
    }

    public function testAuthenticateAdminAsUser()
    {
        $errorMessages = $this->getContainer()->get('config')['authentication']['invalid_credentials'];

        $response = $this->post('/security/generate-token', [
            'username' => 'admin',
            'password' => 'dotadmin',
            'grant_type' => 'password',
            'client_id' => 'frontend',
            'client_secret' => 'frontend',
            'scope' => 'api',
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame(StatusCodeInterface::STATUS_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('error_description', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame($errorMessages['error'], $data['error']);
        $this->assertSame($errorMessages['message'], $data['message']);
        $this->assertSame($errorMessages['error_description'], $data['error_description']);
    }

    public function testAuthenticateUserAsAdmin()
    {
        $errorMessages = $this->getContainer()->get('config')['authentication']['invalid_credentials'];

        $response = $this->post('/security/generate-token', [
            'username' => 'test@dotkernel.com',
            'password' => 'dotkernel',
            'grant_type' => 'password',
            'client_id' => 'admin',
            'client_secret' => 'admin',
            'scope' => 'api',
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame(StatusCodeInterface::STATUS_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('error_description', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame($errorMessages['error'], $data['error']);
        $this->assertSame($errorMessages['message'], $data['message']);
        $this->assertSame($errorMessages['error_description'], $data['error_description']);
    }
}
