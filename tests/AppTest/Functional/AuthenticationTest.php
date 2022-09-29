<?php

namespace AppTest\Functional;

use AppTest\Helper\AbstractFunctionalTest;
use Laminas\Http\Response;
use AppTest\Helper\DatabaseTrait;

/**
 * Class FunctionalTest
 * @package AppTest\Functional
 */
class AuthenticationTest extends AbstractFunctionalTest
{
    use DatabaseTrait;

    public function setUp(): void
    {
        parent::setUp();
    }

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

        $data = json_decode((string)$response->getBody(), true);
        $this->assertSame(Response::STATUS_CODE_400, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('error_description', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame($errorMessages['error'], $data['error']);
        $this->assertSame($errorMessages['message'], $data['message']);
        $this->assertSame($errorMessages['error_description'], $data['error_description']);
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

        $data = json_decode((string)$response->getBody(), true);

        $this->assertSame(Response::STATUS_CODE_200, $response->getStatusCode());
        $this->assertArrayHasKey('token_type', $data);
        $this->assertArrayHasKey('expires_in', $data);
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertSame('Bearer', $data['token_type']);
        $this->assertNotEmpty($data['expires_in']);
        $this->assertNotEmpty($data['access_token']);
        $this->assertNotEmpty($data['refresh_token']);
    }

    public function testRefreshToken()
    {
        $response = $this->post('/security/generate-token', [
            'username' => 'test@dotkernel.com',
            'password' => 'dotkernel',
            'grant_type' => 'password',
            'client_id' => 'frontend',
            'client_secret' => 'frontend',
            'scope' => 'api',
        ]);



    }
}
