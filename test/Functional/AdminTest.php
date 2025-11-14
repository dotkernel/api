<?php

declare(strict_types=1);

namespace ApiTest\Functional;

use BackedEnum;
use Core\Admin\Entity\Admin;
use Core\Admin\Entity\AdminRole;
use Core\Admin\Enum\AdminRoleEnum;
use Core\App\Message;
use Core\App\Service\MailService;
use Core\User\Entity\User;
use Core\User\Entity\UserDetail;
use Core\User\Entity\UserRole;
use Core\User\Enum\UserRoleEnum;
use Core\User\Enum\UserStatusEnum;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function assert;
use function json_decode;
use function sprintf;

class AdminTest extends AbstractFunctionalTest
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUserCannotListAdminAccounts(): void
    {
        /** @var non-empty-string $identity */
        $identity = $this->createUser()->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD);

        $response = $this->get('/admin/account');

        $this->assertResponseForbidden($response);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testUserCannotViewAdminAccount(): void
    {
        $admin = $this->createAdmin();

        /** @var non-empty-string $identity */
        $identity = $this->createUser()->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD);

        $response = $this->get('/admin/' . $admin->getId()->toString());

        $this->assertResponseForbidden($response);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUserCannotCreateAdminAccount(): void
    {
        /** @var non-empty-string $identity */
        $identity = $this->createUser()->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD);

        $response = $this->post('/admin');

        $this->assertResponseForbidden($response);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testUserCannotUpdateAdminAccount(): void
    {
        $admin = $this->createAdmin();

        /** @var non-empty-string $identity */
        $identity = $this->createUser()->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD);

        $response = $this->patch('/admin/' . $admin->getId()->toString());
        $this->assertResponseForbidden($response);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testUserCannotDeleteAdminAccount(): void
    {
        $admin = $this->createAdmin();

        /** @var non-empty-string $identity */
        $identity = $this->createUser()->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD);

        $response = $this->delete('/admin/' . $admin->getId()->toString());
        $this->assertResponseForbidden($response);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAdminCanListAdminAccounts(): void
    {
        /** @var non-empty-string $identity */
        $identity = $this->createAdmin()->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $response = $this->get('/admin/account');

        $this->assertResponseOk($response);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAdminCanViewAdminAccount(): void
    {
        $admin = $this->createAdmin();

        /** @var non-empty-string $identity */
        $identity = $admin->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $response = $this->get('/admin/' . $admin->getId()->toString());
        $data     = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseOk($response);
        $this->assertSame($admin->getId()->toString(), $data['id']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testCannotCreateDuplicateAdminAccount(): void
    {
        $admin = $this->createAdmin();

        /** @var non-empty-string $identity */
        $identity = $admin->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $adminRoleRepository = $this->getEntityManager()->getRepository(AdminRole::class);

        /** @var AdminRole $adminRole */
        $adminRole = $adminRoleRepository->findOneBy(['name' => AdminRoleEnum::Admin]);

        $requestBody = [
            'identity'        => $admin->getIdentity(),
            'password'        => self::DEFAULT_PASSWORD,
            'passwordConfirm' => self::DEFAULT_PASSWORD,
            'firstName'       => $admin->getFirstName(),
            'lastName'        => $admin->getLastName(),
            'status'          => $admin->getStatus()->value,
            'roles'           => [
                [
                    'id' => $adminRole->getId()->toString(),
                ],
            ],
        ];

        $response = $this->post('/admin', $requestBody);
        $data     = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseConflict($response);
        $this->assertArrayHasKey('detail', $data);
        $this->assertSame(Message::DUPLICATE_IDENTITY, $data['detail']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testAdminCanCreateAdminAccount(): void
    {
        $admin = $this->createAdmin();

        /** @var non-empty-string $identity */
        $identity = $admin->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $adminRoleRepository = $this->getEntityManager()->getRepository(AdminRole::class);
        $adminRepository     = $this->getEntityManager()->getRepository(Admin::class);

        $adminRole = $adminRoleRepository->findOneBy(['name' => AdminRoleEnum::Admin]);
        $this->assertInstanceOf(AdminRole::class, $adminRole);

        $requestBody = [
            'identity'        => 'newadmin@test.com',
            'password'        => self::DEFAULT_PASSWORD,
            'passwordConfirm' => self::DEFAULT_PASSWORD,
            'firstName'       => 'Admin',
            'lastName'        => 'Test',
            'status'          => $admin->getStatus()->value,
            'roles'           => [
                [
                    'id' => $adminRole->getId()->toString(),
                ],
            ],
        ];

        $response = $this->post('/admin', $requestBody);

        $this->assertResponseCreated($response);

        $newAdmin = $adminRepository->findOneBy(['identity' => $requestBody['identity']]);
        $this->assertInstanceOf(Admin::class, $newAdmin);
        $this->assertSame($requestBody['identity'], $newAdmin->getIdentity());
        $this->assertSame($requestBody['firstName'], $newAdmin->getFirstName());
        $this->assertSame($requestBody['lastName'], $newAdmin->getLastName());

        foreach ($newAdmin->getRoles() as $role) {
            $this->assertSame($adminRole, $role);
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAdminCanUpdateAdminAccount(): void
    {
        $admin = $this->createAdmin();

        /** @var non-empty-string $identity */
        $identity = $admin->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $updateData = [
            'firstName' => 'Test',
            'lastName'  => 'Admin',
        ];

        $response = $this->patch('/admin/' . $admin->getId()->toString(), $updateData);
        $data     = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseOk($response);
        $this->assertSame($updateData['firstName'], $data['firstName']);
        $this->assertSame($updateData['lastName'], $data['lastName']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testAdminCanDeleteAdminAccount(): void
    {
        $admin = $this->createAdmin();

        /** @var non-empty-string $identity */
        $identity = $admin->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $response = $this->delete('/admin/' . $admin->getId()->toString());

        $this->assertResponseNoContent($response);

        $adminRepository = $this->getEntityManager()->getRepository(Admin::class);
        $admin           = $adminRepository->find($admin->getId()->toString());

        $this->assertEmpty($admin);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAdminCanViewPersonalAccount(): void
    {
        $admin = $this->createAdmin();

        /** @var non-empty-string $identity */
        $identity = $admin->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $response = $this->get('/admin/account');
        $data     = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseOk($response);
        $this->assertSame($admin->getId()->toString(), $data['id']);
        $this->assertSame($admin->getIdentity(), $data['identity']);
        $this->assertSame($admin->getFirstName(), $data['firstName']);
        $this->assertSame($admin->getLastName(), $data['lastName']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAdminCanUpdatePersonalAccount(): void
    {
        /** @var non-empty-string $identity */
        $identity = $this->createAdmin()->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $updateData = [
            'firstName' => 'test',
            'lastName'  => 'admin',
        ];

        $response = $this->patch('/admin/account', $updateData);
        $data     = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseOk($response);
        $this->assertSame($updateData['firstName'], $data['firstName']);
        $this->assertSame($updateData['lastName'], $data['lastName']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAdminCanListAdminRoles(): void
    {
        /** @var non-empty-string $identity */
        $identity = $this->createAdmin()->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $response = $this->get('/admin/role');

        $this->assertResponseOk($response);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testAdminCanViewAdminRole(): void
    {
        $admin = $this->createAdmin();

        /** @var non-empty-string $identity */
        $identity = $admin->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $adminRole = $admin->getRoles()[0];

        $response = $this->get('/admin/role/' . $adminRole->getId()->toString());
        $data     = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseOk($response);
        $this->assertSame($adminRole->getId()->toString(), $data['id']);
        $this->assertSame($adminRole->getName()->value, $data['name']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testAdminCreateUserAccountDuplicateEmail(): void
    {
        $this->createUser(['detail' => ['email' => 'user1@test.com']]);

        /** @var non-empty-string $identity */
        $identity = $this->createAdmin()->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $userRole = $this->findUserRole(UserRoleEnum::User);
        $this->assertInstanceOf(UserRole::class, $userRole);

        $userData = [
            'identity'        => 'test@user.com',
            'password'        => self::DEFAULT_PASSWORD,
            'passwordConfirm' => self::DEFAULT_PASSWORD,
            'status'          => UserStatusEnum::Pending->value,
            'detail'          => [
                'firstName' => 'User',
                'lastName'  => 'Test',
                'email'     => 'user1@test.com',
            ],
            'roles'           => [
                ['id' => $userRole->getId()->toString()],
            ],
        ];

        $response = $this->post('/user', $userData);
        $this->assertResponseConflict($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('detail', $data);
        $this->assertSame(Message::DUPLICATE_EMAIL, $data['detail']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws Exception
     */
    public function testAdminCanCreateUserAccount(): void
    {
        /** @var non-empty-string $identity */
        $identity = $this->createAdmin()->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $userRoleRepository = $this->getEntityManager()->getRepository(UserRole::class);
        $userRole           = $userRoleRepository->findOneBy(['name' => UserRoleEnum::User]);
        $this->assertInstanceOf(UserRole::class, $userRole);

        $mailService = $this->createMock(MailService::class);
        $this->replaceService(MailService::class, $mailService);

        $userData = [
            'identity'        => 'test@user.com',
            'password'        => self::DEFAULT_PASSWORD,
            'passwordConfirm' => self::DEFAULT_PASSWORD,
            'status'          => UserStatusEnum::Pending->value,
            'detail'          => [
                'firstName' => 'User',
                'lastName'  => 'Test',
                'email'     => 'test@user.com',
            ],
            'roles'           => [
                ['id' => $userRole->getId()->toString()],
            ],
        ];

        $response = $this->post('/user', $userData);
        $data     = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseCreated($response);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('hash', $data);
        $this->assertArrayHasKey('identity', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('avatar', $data);
        $this->assertArrayHasKey('detail', $data);
        $this->assertArrayHasKey('roles', $data);
        $this->assertNotEmpty($data['id']);
        $this->assertNotEmpty($data['hash']);
        $this->assertSame($userData['identity'], $data['identity']);
        $this->assertSame(UserStatusEnum::Pending->value, $data['status']);
        $this->assertEmpty($data['avatar']);
        $this->assertArrayHasKey('firstName', $data['detail']);
        $this->assertArrayHasKey('lastName', $data['detail']);
        $this->assertArrayHasKey('email', $data['detail']);
        $this->assertSame($userData['detail']['firstName'], $data['detail']['firstName']);
        $this->assertSame($userData['detail']['lastName'], $data['detail']['lastName']);
        $this->assertSame($userData['detail']['email'], $data['detail']['email']);
        $this->assertNotEmpty($data['roles']);
        $this->assertSame($userRole->getId()->toString(), $data['roles'][0]['id']);
        $this->assertSame($userRole->getName()->value, $data['roles'][0]['name']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testAdminCanActiveUserAccount(): void
    {
        $user = $this->createUser([
            'status' => UserStatusEnum::Pending,
        ]);

        /** @var non-empty-string $identity */
        $identity = $this->createAdmin()->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $this->assertFalse($user->isActive());
        $response = $this->patch(sprintf('/user/%s/activate', $user->getId()->toString()));

        $this->assertResponseOk($response);

        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $user           = $userRepository->find($user->getId()->toString());
        assert($user instanceof User);

        $this->assertTrue($user->isActive());
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testAdminCanDeleteUserAccount(): void
    {
        $user = $this->createUser();

        /** @var non-empty-string $identity */
        $identity = $this->createAdmin()->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $response = $this->delete('/user/' . $user->getId()->toString());

        $this->assertResponseNoContent($response);

        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $user           = $userRepository->find($user->getId()->toString());
        assert($user instanceof User);

        $this->assertTrue($user->isDeleted());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAdminCanListUserAccounts(): void
    {
        /** @var non-empty-string $identity */
        $identity = $this->createAdmin()->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $response = $this->get('/user');

        $this->assertResponseOk($response);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testAdminUpdateUserAccountDuplicateEmail(): void
    {
        $user1 = $this->createUser(['identity' => 'user1@test.com', 'detail' => ['email' => 'user1@test.com']]);
        $user2 = $this->createUser(['identity' => 'user2@test.com', 'detail' => ['email' => 'user2@test.com']]);
        $this->assertInstanceOf(UserDetail::class, $user1->getDetail());
        $this->assertInstanceOf(UserDetail::class, $user2->getDetail());

        /** @var non-empty-string $identity */
        $identity = $this->createAdmin()->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $response = $this->patch('/user/' . $user2->getId()->toString(), [
            'detail' => [
                'email' => $user1->getDetail()->getEmail(),
            ],
        ]);

        $this->assertResponseConflict($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('detail', $data);
        $this->assertSame(Message::DUPLICATE_EMAIL, $data['detail']);

        $userDetailRepository = $this->getEntityManager()->getRepository(UserDetail::class);
        $userDetail           = $userDetailRepository->find($user2->getDetail()->getId());
        assert($userDetail instanceof UserDetail);

        $this->assertSame($user2->getDetail()->getEmail(), $userDetail->getEmail());
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testAdminCanUpdateUserAccount(): void
    {
        $userRole = $this->findUserRole(UserRoleEnum::User);
        $this->assertInstanceOf(UserRole::class, $userRole);

        $user = $this->createUser();

        /** @var non-empty-string $identity */
        $identity = $this->createAdmin()->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $updateData = [
            'detail' => [
                'firstName' => 'Foo',
                'lastName'  => 'Bar',
                'email'     => 'foobar@dotkernel.com',
            ],
            'status' => UserStatusEnum::Active->value,
            'roles'  => [
                [
                    'id' => $userRole->getId()->toString(),
                ],
            ],
        ];

        $response = $this->patch('/user/' . $user->getId()->toString(), $updateData);

        $this->assertResponseOk($response);

        $data = json_decode($response->getBody()->getContents(), true);

        $status = UserStatusEnum::tryFrom($updateData['status']);
        $this->assertInstanceOf(BackedEnum::class, $status);
        $this->assertSame($updateData['detail']['firstName'], $data['detail']['firstName']);
        $this->assertSame($updateData['detail']['lastName'], $data['detail']['lastName']);
        $this->assertSame($updateData['roles'][0]['id'], $data['roles'][0]['id']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testAdminCanViewUserAccount(): void
    {
        $user = $this->createUser();

        /** @var non-empty-string $identity */
        $identity = $this->createAdmin()->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $response = $this->get('/user/' . $user->getId()->toString());

        $this->assertResponseOk($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertSame($user->getId()->toString(), $data['id']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAdminViewNotFoundUserAccount(): void
    {
        /** @var non-empty-string $identity */
        $identity = $this->createAdmin()->getIdentity();
        $this->loginAs($identity, self::DEFAULT_PASSWORD, 'admin', 'admin');

        $response = $this->get('/user/' . (new User())->getId()->toString());

        $this->assertResponseNotFound($response);
    }
}
