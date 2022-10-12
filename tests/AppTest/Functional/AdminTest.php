<?php

namespace AppTest\Functional;

use Api\Admin\Entity\Admin;
use Api\Admin\Entity\AdminRole;
use Api\App\Entity\RoleInterface;
use Api\App\Message;
use Api\User\Entity\User;
use Api\User\Entity\UserDetail;
use Api\User\Entity\UserRole;
use AppTest\Functional\Traits\AuthenticationTrait;
use AppTest\Functional\Traits\DatabaseTrait;

/**
 * Class AdminTest
 * @package AppTest\Functional
 */
class AdminTest extends AbstractFunctionalTest
{
    use DatabaseTrait, AuthenticationTrait;

//    public function testUserCannotListAdminAccounts()
//    {
//        $user = $this->createUser([], true);
//        $this->loginAs($user->getIdentity(), '123456');
//
//        $response = $this->get('/admin/my-account');
//
//        $this->assertResponseForbidden($response);
//    }
//
//    public function testUserCannotViewAdminAccount()
//    {
//        $user = $this->createUser([], true);
//        $admin = $this->createAdmin();
//
//        $this->loginAs($user->getIdentity(), '123456');
//
//        $response = $this->get('/admin/' . $admin->getUuid()->toString());
//
//        $this->assertResponseForbidden($response);
//    }
//
//    public function testUserCannotCreateAdminAccount()
//    {
//        $user = $this->createUser([], true);
//        $this->loginAs($user->getIdentity(), '123456');
//
//        $response = $this->post('/admin', []);
//
//        $this->assertResponseForbidden($response);
//    }
//
//    public function testUserCannotUpdateAdminAccount()
//    {
//        $user = $this->createUser([], true);
//        $admin = $this->createAdmin();
//
//        $this->loginAs($user->getIdentity(), '123456');
//
//        $response = $this->patch('/admin/' . $admin->getUuid()->toString(), []);
//        $this->assertResponseForbidden($response);
//    }
//
//    public function testUserCannotDeleteAdminAccount()
//    {
//        $user = $this->createUser([], true);
//        $admin = $this->createAdmin();
//
//        $this->loginAs($user->getIdentity(), '123456');
//
//        $response = $this->delete('/admin/' . $admin->getUuid()->toString());
//        $this->assertResponseForbidden($response);
//    }
//
//    public function testAdminCanListAdminAccounts()
//    {
//        $admin = $this->createAdmin();
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//
//        $response = $this->get('/admin/my-account');
//
//        $this->assertResponseOk($response);
//    }
//
//    public function testAdminCanViewAdminAccount()
//    {
//        $admin = $this->createAdmin();
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//
//        $response = $this->get('/admin/' . $admin->getUuid()->toString());
//        $data = json_decode($response->getBody()->getContents(), true);
//
//        $this->assertResponseOk($response);
//        $this->assertSame($admin->getUuid()->toString(), $data['uuid']);
//    }
//
//    public function testCannotCreateDuplicateAdminAccount()
//    {
//        $admin = $this->createAdmin();
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//
//        $adminRoleRepository = $this->getEntityManager()->getRepository(AdminRole::class);
//
//        /** @var AdminRole $adminRole */
//        $adminRole = $adminRoleRepository->findOneBy(['name' => AdminRole::ROLE_ADMIN]);
//
//        $requestBody = [
//            'identity' => $admin->getIdentity(),
//            'password' => '123456',
//            'passwordConfirm' => '123456',
//            'firstName' => $admin->getFirstName(),
//            'lastName' => $admin->getLastName(),
//            'roles' => [
//                [
//                    'uuid' => $adminRole->getUuid()->toString()
//                ]
//            ]
//        ];
//
//        $response = $this->post('/admin', $requestBody);
//        $data = json_decode($response->getBody()->getContents(), true);
//
//        $this->assertResponseBadRequest($response);
//        $this->assertArrayHasKey('error', $data);
//        $this->assertArrayHasKey('messages', $data['error']);
//        $this->assertSame(Message::DUPLICATE_IDENTITY, $data['error']['messages'][0]);
//    }
//
//    public function testAdminCanCreateAdminAccount()
//    {
//        $admin = $this->createAdmin();
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//
//        $adminRoleRepository = $this->getEntityManager()->getRepository(AdminRole::class);
//        $adminRepository = $this->getEntityManager()->getRepository(Admin::class);
//
//        $adminRole = $adminRoleRepository->findOneBy(['name' => AdminRole::ROLE_ADMIN]);
//
//        $requestBody = [
//            'identity' => 'newadmin@test.com',
//            'password' => '123456',
//            'passwordConfirm' => '123456',
//            'firstName' => 'Admin',
//            'lastName' => 'Test',
//            'roles' => [
//                [
//                    'uuid' => $adminRole->getUuid()->toString()
//                ]
//            ]
//        ];
//
//        $response = $this->post('/admin', $requestBody);
//
//        $this->assertResponseOk($response);
//
//        $newAdmin = $adminRepository->findOneBy(['identity' => $requestBody['identity']]);
//        $this->assertSame($requestBody['identity'], $newAdmin->getIdentity());
//        $this->assertSame($requestBody['firstName'], $newAdmin->getFirstName());
//        $this->assertSame($requestBody['lastName'], $newAdmin->getLastName());
//
//        $newAdmin->getRoles()->map(function ($role) use ($adminRole) {
//            $this->assertSame($adminRole, $role);
//        });
//    }

    public function testAdminCanUpdateAdminAccount()
    {
        $admin = $this->createAdmin();
        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');

        $this->swap();
        $updateData = [
            'firstName' => 'Test',
            'lastName' => 'Admin',
        ];

        $response = $this->patch('/admin/' . $admin->getUuid()->toString(), $updateData);
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseOk($response);
        $this->assertSame($updateData['firstName'], $data['firstName']);
        $this->assertSame($updateData['lastName'], $data['lastName']);
    }
//
//    public function testAdminCanDeleteAdminAccount()
//    {
//        $admin = $this->createAdmin();
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//
//        $response = $this->delete('/admin/' . $admin->getUuid()->toString());
//
//        $this->assertResponseOk($response);
//
//        $adminRepository = $this->getEntityManager()->getRepository(Admin::class);
//        $admin = $adminRepository->find($admin->getUuid()->toString());
//
//        $this->assertEmpty($admin);
//    }
//
//    public function testAdminCanViewPersonalAccount()
//    {
//        $admin = $this->createAdmin();
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//
//        $response = $this->get('/admin/my-account');
//        $data = json_decode($response->getBody()->getContents(), true);
//
//        $this->assertResponseOk($response);
//        $this->assertSame($admin->getUuid()->toString(), $data['uuid']);
//        $this->assertSame($admin->getIdentity(), $data['identity']);
//        $this->assertSame($admin->getFirstName(), $data['firstName']);
//        $this->assertSame($admin->getLastName(), $data['lastName']);
//    }
//
//    public function testAdminCanUpdatePersonalAccount()
//    {
//        $admin = $this->createAdmin();
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//
//        $updateData = [
//            'firstName' => 'test',
//            'lastName' => 'admin',
//        ];
//
//        $response = $this->patch('/admin/my-account', $updateData);
//        $data = json_decode($response->getBody()->getContents(), true);
//
//        $this->assertResponseOk($response);
//        $this->assertSame($updateData['firstName'], $data['firstName']);
//        $this->assertSame($updateData['lastName'], $data['lastName']);
//    }
//
//    public function testAdminCanListAdminRoles()
//    {
//        $admin = $this->createAdmin();
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//
//        $response = $this->get('/admin/role');
//
//        $this->assertResponseOk($response);
//    }
//
//    public function testAdminCanViewAdminRole()
//    {
//        $admin = $this->createAdmin();
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//
//        $adminRole = new AdminRole();
//        $adminRole->setName('new_admin_role');
//        $this->getEntityManager()->persist($adminRole);
//        $this->getEntityManager()->flush();
//
//        $response = $this->get('/admin/role/' . $adminRole->getUuid()->toString());
//        $data = json_decode($response->getBody()->getContents(), true);
//
//        $this->assertResponseOk($response);
//        $this->assertSame($adminRole->getUuid()->toString(), $data['uuid']);
//        $this->assertSame($adminRole->getName(), $data['name']);
//    }
//
//    public function testAdminCreateUserAccountDuplicateEmail()
//    {
//        $admin = $this->createAdmin();
//        $this->createUser(['detail' => ['email' => 'user1@test.com']]);
//
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//
//        $userData = [
//            'identity' => 'test@user.com',
//            'password' => '123456',
//            'passwordConfirm' => '123456',
//            'status' => 'pending',
//            'detail' => [
//                'firstName' => 'User',
//                'lastName' => 'Test',
//                'email' => 'user1@test.com',
//            ],
//        ];
//
//        $response = $this->post('/user', $userData);
//        $this->assertResponseBadRequest($response);
//
//        $data = json_decode($response->getBody()->getContents(), true);
//        $this->assertArrayHasKey('error', $data);
//        $this->assertArrayHasKey('messages', $data['error']);
//        $this->assertSame(Message::DUPLICATE_EMAIL, $data['error']['messages'][0]);
//    }
//
//    public function testAdminCanCreateUserAccount()
//    {
//        $admin = $this->createAdmin();
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//        $userRoleRepository = $this->getEntityManager()->getRepository(UserRole::class);
//        $userRole = $userRoleRepository->findOneBy(['name' => UserRole::ROLE_USER]);
//
//        $userData = [
//            'identity' => 'test@user.com',
//            'password' => '123456',
//            'passwordConfirm' => '123456',
//            'status' => 'pending',
//            'detail' => [
//                'firstName' => 'User',
//                'lastName' => 'Test',
//                'email' => 'test@user.com',
//            ],
//        ];
//
//        $response = $this->post('/user', $userData);
//        $data = json_decode($response->getBody()->getContents(), true);
//
//        $this->assertResponseOk($response);
//        $this->assertArrayHasKey('uuid', $data);
//        $this->assertArrayHasKey('hash', $data);
//        $this->assertArrayHasKey('identity', $data);
//        $this->assertArrayHasKey('status', $data);
//        $this->assertArrayHasKey('isDeleted', $data);
//        $this->assertArrayHasKey('avatar', $data);
//        $this->assertArrayHasKey('detail', $data);
//        $this->assertArrayHasKey('roles', $data);
//        $this->assertNotEmpty($data['uuid']);
//        $this->assertNotEmpty($data['hash']);
//        $this->assertSame($userData['identity'], $data['identity']);
//        $this->assertSame(User::STATUS_PENDING, $data['status']);
//        $this->assertFalse($data['isDeleted']);
//        $this->assertEmpty($data['avatar']);
//        $this->assertEmpty($data['resetPasswords']);
//        $this->assertArrayHasKey('firstName', $data['detail']);
//        $this->assertArrayHasKey('lastName', $data['detail']);
//        $this->assertArrayHasKey('email', $data['detail']);
//        $this->assertSame($userData['detail']['firstName'], $data['detail']['firstName']);
//        $this->assertSame($userData['detail']['lastName'], $data['detail']['lastName']);
//        $this->assertSame($userData['detail']['email'], $data['detail']['email']);
//        $this->assertNotEmpty($data['roles']);
//        $this->assertSame($userRole->getUuid()->toString(), $data['roles'][0]['uuid']);
//        $this->assertSame($userRole->getName(), $data['roles'][0]['name']);
//    }
//
//    public function testAdminCanActiveUserAccount()
//    {
//        $admin = $this->createAdmin();
//        $user = $this->createUser();
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//
//        $this->assertFalse($user->isActive());
//        $response = $this->post(sprintf('/user/%s/activate', $user->getUuid()->toString()));
//
//        $this->assertResponseOk($response);
//
//        $userRepository = $this->getEntityManager()->getRepository(User::class);
//        $user = $userRepository->find($user->getUuid()->toString());
//
//        $this->assertTrue($user->isActive());
//    }
//
//    public function testAdminCanDeleteUserAccount()
//    {
//        $admin = $this->createAdmin();
//        $user = $this->createUser();
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//
//        $response = $this->delete('/user/' . $user->getUuid()->toString());
//
//        $this->assertResponseOk($response);
//
//        $userRepository = $this->getEntityManager()->getRepository(User::class);
//        $user = $userRepository->find($user->getUuid()->toString());
//
//        $this->assertTrue($user->isDeleted());
//    }
//
//    public function testAdminCanListUserAccounts()
//    {
//        $admin = $this->createAdmin();
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//
//        $response = $this->get('/user');
//
//        $this->assertResponseOk($response);
//    }
//
//    public function testAdminUpdateUserAccountDuplicateEmail()
//    {
//        $admin = $this->createAdmin();
//        $user1 = $this->createUser(['identity' => 'user1@test.com', 'detail' => ['email' => 'user1@test.com']]);
//        $user2 = $this->createUser(['identity' => 'user2@test.com', 'detail' => ['email' => 'user2@test.com']]);
//
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//
//        $response = $this->patch('/user/' . $user2->getUuid()->toString(), [
//            'detail' => [
//                'email' => $user1->getDetail()->getEmail(),
//            ]
//        ]);
//
//        $this->assertResponseBadRequest($response);
//
//        $data = json_decode($response->getBody()->getContents(), true);
//        $this->assertArrayHasKey('error', $data);
//        $this->assertArrayHasKey('messages', $data['error']);
//        $this->assertSame(Message::DUPLICATE_EMAIL, $data['error']['messages'][0]);
//
//        $userDetailRepository = $this->getEntityManager()->getRepository(UserDetail::class);
//        $userDetail = $userDetailRepository->find($user2->getDetail()->getUuid());
//        $this->assertSame($user2->getDetail()->getEmail(), $userDetail->getEmail());
//    }
//
//    public function testAdminCanUpdateUserAccount()
//    {
//        $userRole = new UserRole();
//        $userRole->setName('new_role');
//        $this->getEntityManager()->persist($userRole);
//        $this->getEntityManager()->flush();
//
//        $user = $this->createUser();
//        $admin = $this->createAdmin();
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//
//        $updateData = [
//            'detail' => [
//                'firstName' => 'John',
//                'lastName' => 'Doe',
//                'email' => 'john@doe.com',
//            ],
//            'status' => User::STATUS_ACTIVE,
//            'roles' => [
//                [
//                    'uuid' => $userRole->getUuid()->toString(),
//                ]
//            ]
//        ];
//
//        $response = $this->patch('/user/' . $user->getUuid()->toString(), $updateData);
//
//        $this->assertResponseOk($response);
//
//        $data = json_decode($response->getBody()->getContents(), true);
//
//        $this->assertSame($updateData['status'], $data['status']);
//        $this->assertSame($updateData['detail']['firstName'], $data['detail']['firstName']);
//        $this->assertSame($updateData['detail']['lastName'], $data['detail']['lastName']);
//        $this->assertSame($updateData['roles'][0]['uuid'], $data['roles'][0]['uuid']);
//    }
//
//    public function testAdminCanViewUserAccount()
//    {
//        $user = $this->createUser();
//        $admin = $this->createAdmin();
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//
//        $response = $this->get('/user/' . $user->getUuid()->toString());
//
//        $this->assertResponseOk($response);
//
//        $data = json_decode($response->getBody()->getContents(), true);
//        $this->assertSame($user->getUuid()->toString(), $data['uuid']);
//    }
//
//    public function testAdminViewNotFoundUserAccount()
//    {
//        $user = new User();
//        $admin = $this->createAdmin();
//        $this->loginAs($admin->getIdentity(), '123456', 'admin', 'admin');
//
//        $response = $this->get('/user/' . $user->getUuid()->toString());
//
//        $this->assertResponseNotFound($response);
//    }

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

    private function createUser(array $data = [], bool $activated = false): User
    {
        /** @var RoleInterface $userRole */
        $userRoleRepository = $this->getEntityManager()->getRepository(UserRole::class);
        $userRole = $userRoleRepository->findOneBy(['name' => UserRole::ROLE_USER]);

        $user = new User();
        $userDetail = new UserDetail();

        $user->setIdentity($data['identity'] ?? 'user@test.com');
        $user->setPassword(password_hash($data['password'] ?? '123456', PASSWORD_DEFAULT));
        if ($activated) {
            $user->activate();
        }
        $userDetail->setFirstName($data['detail']['firstName'] ?? 'Test');
        $userDetail->setLastName($data['detail']['firstName'] ?? 'User');
        $userDetail->setEmail($data['detail']['email'] ?? 'user@test.com');
        $userDetail->setUser($user);

        $user->setDetail($userDetail);
        $user->addRole($userRole);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }
}
