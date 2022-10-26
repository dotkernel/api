<?php

namespace AppTest\Functional;

use Api\Admin\Entity\Admin;
use Api\Admin\Entity\AdminRole;
use Api\App\Entity\RoleInterface;
use Api\User\Entity\User;
use Api\User\Entity\UserDetail;
use Api\User\Entity\UserRole;
use AppTest\Functional\Traits\AuthenticationTrait;
use AppTest\Functional\Traits\DatabaseTrait;

/**
 * Class FunctionalTest
 * @package AppTest\Functional
 */
class AuthenticationTest extends AbstractFunctionalTest
{
    use DatabaseTrait, AuthenticationTrait;

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

        $this->assertResponseBadRequest($response);
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

        $this->assertResponseBadRequest($response);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('error_description', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame($errorMessages['error'], $data['error']);
        $this->assertSame($errorMessages['message'], $data['message']);
        $this->assertSame($errorMessages['error_description'], $data['error_description']);
    }

    public function testAuthenticateAdmin()
    {
        $admin = $this->createAdmin();

        $response = $this->post('/security/generate-token', [
            'username' => $admin->getIdentity(),
            'password' => '123456',
            'grant_type' => 'password',
            'client_id' => 'admin',
            'client_secret' => 'admin',
            'scope' => 'api',
        ]);

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

    public function testAuthenticateUser()
    {
        $user = $this->createUser();
        $response = $this->post('/security/generate-token', [
            'username' => $user->getIdentity(),
            'password' => '123456',
            'grant_type' => 'password',
            'client_id' => 'frontend',
            'client_secret' => 'frontend',
            'scope' => 'api',
        ]);

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

        $this->assertResponseUnauthorized($response);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('error_description', $data);
        $this->assertArrayHasKey('hint', $data);
        $this->assertArrayHasKey('message', $data);

        $this->assertSame('invalid_request', $data['error']);
    }

    public function testRefreshToken()
    {
        $user = $this->createUser();
        $this->loginAs($user->getIdentity(), '123456');

        $response = $this->post('/security/refresh-token', [
            'grant_type' => 'refresh_token',
            'client_id' => 'frontend',
            'client_secret' => 'frontend',
            'scope' => 'api',
            'refresh_token' => $this->getRefreshToken(),
        ]);

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

    public function testAdminCannotAuthenticateAsUser()
    {
        $admin = $this->createAdmin();
        $errorMessages = $this->getContainer()->get('config')['authentication']['invalid_credentials'];

        $response = $this->post('/security/generate-token', [
            'username' => $admin->getIdentity(),
            'password' => '123456',
            'grant_type' => 'password',
            'client_id' => 'frontend',
            'client_secret' => 'frontend',
            'scope' => 'api',
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseBadRequest($response);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('error_description', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame($errorMessages['error'], $data['error']);
        $this->assertSame($errorMessages['message'], $data['message']);
        $this->assertSame($errorMessages['error_description'], $data['error_description']);
    }

    public function testUserCannotAuthenticateAsAdmin()
    {
        $user = $this->createUser();
        $errorMessages = $this->getContainer()->get('config')['authentication']['invalid_credentials'];

        $response = $this->post('/security/generate-token', [
            'username' => $user->getIdentity(),
            'password' => '123456',
            'grant_type' => 'password',
            'client_id' => 'admin',
            'client_secret' => 'admin',
            'scope' => 'api',
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseBadRequest($response);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('error_description', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame($errorMessages['error'], $data['error']);
        $this->assertSame($errorMessages['message'], $data['message']);
        $this->assertSame($errorMessages['error_description'], $data['error_description']);
    }

    private function createAdmin(): Admin
    {
        /** @var RoleInterface $adminRole */
        $adminRoleRepository = $this->getEntityManager()->getRepository(AdminRole::class);
        $adminRole = $adminRoleRepository->findOneBy(['name' => AdminRole::ROLE_ADMIN]);

        $admin = new Admin();
        $admin->setIdentity('admin@test.com');
        $admin->setPassword(password_hash('123456', PASSWORD_DEFAULT));
        $admin->setFirstName('Admin');
        $admin->setLastName('Test');
        $admin->addRole($adminRole);

        $this->getEntityManager()->persist($admin);
        $this->getEntityManager()->flush();

        return $admin;
    }

    private function createUser(): User
    {
        /** @var RoleInterface $userRole */
        $userRoleRepository = $this->getEntityManager()->getRepository(UserRole::class);
        $userRole = $userRoleRepository->findOneBy(['name' => UserRole::ROLE_USER]);

        $user = new User();
        $userDetail = new UserDetail();

        $user->setIdentity('user@test.com');
        $user->setPassword(password_hash('123456', PASSWORD_DEFAULT));
        $user->activate();
        $userDetail->setFirstName('Test');
        $userDetail->setLastName('User');
        $userDetail->setEmail('user@test.com');
        $userDetail->setUser($user);

        $user->setDetail($userDetail);
        $user->addRole($userRole);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }
}
