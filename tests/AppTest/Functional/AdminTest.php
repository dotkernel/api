<?php

namespace AppTest\Functional;

use Api\Admin\Entity\Admin;
use Api\Admin\Entity\AdminRole;
use Api\App\Entity\RoleInterface;
use Api\User\Entity\User;
use Api\User\Entity\UserDetail;
use Api\User\Entity\UserRole;
use AppTest\Functional\Traits\DatabaseTrait;
use Fig\Http\Message\StatusCodeInterface;

/**
 * Class AdminTest
 * @package AppTest\Functional
 */
class AdminTest extends AbstractFunctionalTest
{
    use DatabaseTrait;

    public function testUserCannotListAdminAccounts()
    {
        $user = $this->createUser();
        $this->loginAs($user->getIdentity(), '123456');

        $response = $this->get('/admin/my-account');

        $this->assertSame(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
    }

    public function testUserCannotViewAdminAccount()
    {
        $user = $this->createUser();
        $admin = $this->createAdmin();

        $this->loginAs($user->getIdentity(), '123456');

        $response = $this->get('/admin/' . $admin->getUuid()->toString());

        $this->assertSame(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
    }

    public function testUserCannotCreateAdminAccount()
    {
        $user = $this->createUser();
        $this->loginAs($user->getIdentity(), '123456');

        $response = $this->post('/admin', []);

        $this->assertSame(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
    }

    public function testUserCannotUpdateAdminAccount()
    {
        $user = $this->createUser();
        $admin = $this->createAdmin();

        $this->loginAs($user->getIdentity(), '123456');

        $response = $this->patch('/admin/' . $admin->getUuid()->toString(), []);
        $this->assertSame(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
    }

    public function testUserCannotDeleteAdminAccount()
    {
        $user = $this->createUser();
        $admin = $this->createAdmin();

        $this->loginAs($user->getIdentity(), '123456');

        $response = $this->delete('/admin/' . $admin->getUuid()->toString());
        $this->assertSame(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
    }

    public function testAdminCanListAdminAccounts()
    {
        $admin = $this->createAdmin();

        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');

        $response = $this->get('/admin/my-account');

        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    public function testAdminCanViewAdminAccount()
    {
        $admin = $this->createAdmin();

        $this->loginAs('admin', 'dotadmin', 'admin', 'admin');

        $response = $this->get('/admin/' . $admin->getUuid()->toString());
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $this->assertSame($admin->getUuid()->toString(), $data['uuid']);
    }

    public function testAdminCanCreateAdminAccount()
    {
        $this->loginAs('admin', 'dotadmin', 'admin', 'admin');

        $adminRoleRepository = $this->getEntityManager()->getRepository(AdminRole::class);
        $adminRepository = $this->getEntityManager()->getRepository(Admin::class);

        /** @var AdminRole $adminRole */
        $adminRole = $adminRoleRepository->findOneBy(['name' => AdminRole::ROLE_ADMIN]);

        $requestBody = [
            'identity' => 'admin@test.com',
            'password' => '123456',
            'passwordConfirm' => '123456',
            'firstName' => 'Admin',
            'lastName' => 'Test',
            'roles' => [
                [
                    'uuid' => $adminRole->getUuid()->toString()
                ]
            ]
        ];

        $response = $this->post('/admin', $requestBody);

        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        /** @var Admin $newAdmin */
        $newAdmin = $adminRepository->findOneBy(['identity' => $requestBody['identity']]);
        $this->assertSame($requestBody['identity'], $newAdmin->getIdentity());
        $this->assertSame($requestBody['firstName'], $newAdmin->getFirstName());
        $this->assertSame($requestBody['lastName'], $newAdmin->getLastName());

        $newAdmin->getRoles()->map(function ($role) use ($adminRole) {
            $this->assertSame($adminRole, $role);
        });
    }

    public function testAdminCanUpdateAdminAccount()
    {
        $admin = $this->createAdmin();

        $this->loginAs('admin', 'dotadmin', 'admin', 'admin');

        $updateData = [
            'firstName' => 'Test',
            'lastName' => 'Admin',
        ];

        $response = $this->patch('/admin/' . $admin->getUuid()->toString(), $updateData);
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $this->assertSame($updateData['firstName'], $data['firstName']);
        $this->assertSame($updateData['lastName'], $data['lastName']);
    }

    public function testAdminCanDeleteAdminAccount()
    {
        $admin = $this->createAdmin();

        $this->loginAs('admin', 'dotadmin', 'admin', 'admin');

        $response = $this->delete('/admin/' . $admin->getUuid()->toString());

        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $adminRepository = $this->getEntityManager()->getRepository(Admin::class);
        $admin = $adminRepository->find($admin->getUuid()->toString());

        $this->assertEmpty($admin);
    }

    public function testAdminCanViewPersonalAccount()
    {
        $admin = $this->createAdmin();

        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');

        $response = $this->get('/admin/my-account');
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $this->assertSame($admin->getUuid()->toString(), $data['uuid']);
        $this->assertSame($admin->getIdentity(), $data['identity']);
        $this->assertSame($admin->getFirstName(), $data['firstName']);
        $this->assertSame($admin->getLastName(), $data['lastName']);
    }

    public function testAdminCanUpdatePersonalAccount()
    {
        $admin = $this->createAdmin();

        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');

        $updateData = [
            'firstName' => 'test',
            'lastName' => 'admin',
        ];

        $response = $this->patch('/admin/my-account', $updateData);
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $this->assertSame($updateData['firstName'], $data['firstName']);
        $this->assertSame($updateData['lastName'], $data['lastName']);
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
