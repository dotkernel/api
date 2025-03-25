<?php

declare(strict_types=1);

namespace ApiTest\Unit\User\Service;

use Core\App\Exception\BadRequestException;
use Core\App\Exception\ConflictException;
use Core\App\Exception\NotFoundException;
use Core\Security\Repository\OAuthAccessTokenRepository;
use Core\Security\Repository\OAuthRefreshTokenRepository;
use Core\User\Entity\User;
use Core\User\Entity\UserDetail;
use Core\User\Entity\UserRole;
use Core\User\Enum\UserStatusEnum;
use Core\User\Repository\UserDetailRepository;
use Core\User\Repository\UserRepository;
use Core\User\Repository\UserRoleRepository;
use Core\User\Service\UserService;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function count;

class UserServiceTest extends TestCase
{
    private UserService $subject;
    private UserRepository&MockObject $userRepository;
    private UserDetailRepository&MockObject $userDetailRepository;
    private UserRoleRepository&MockObject $userRoleRepository;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function setUp(): void
    {
        $oAuthAccessTokenRepository  = $this->createMock(OAuthAccessTokenRepository::class);
        $oAuthRefreshTokenRepository = $this->createMock(OAuthRefreshTokenRepository::class);
        $this->userRepository        = $this->createMock(UserRepository::class);
        $this->userDetailRepository  = $this->createMock(UserDetailRepository::class);
        $this->userRoleRepository    = $this->createMock(UserRoleRepository::class);
        $this->subject               = new UserService(
            $oAuthAccessTokenRepository,
            $oAuthRefreshTokenRepository,
            $this->userRepository,
            $this->userDetailRepository,
            $this->userRoleRepository,
            []
        );
    }

    /**
     * @throws NotFoundException
     * @throws ConflictException
     */
    public function testCreateUserThrowsExceptionDuplicateIdentity(): void
    {
        $request = $this->getUser();
        $this->userRepository
            ->method('findOneBy')
            ->willReturn($this->getUserEntity(['identity' => $request['identity']]));

        $this->expectException(Exception::class);

        $this->subject->createUser($request);
    }

    /**
     * @throws Exception
     */
    public function testCreateUserWithMultipleRoles(): void
    {
        $data = $this->getUser([
            'roles' => [
                [
                    'uuid' => 'uuid',
                    'name' => UserRole::ROLE_GUEST,
                ],
                [
                    'uuid' => 'uuid',
                    'name' => UserRole::ROLE_USER,
                ],
            ],
        ]);

        $this->userRoleRepository->method('find')->willReturn(new UserRole());
        $this->userRepository->method('saveUser')->willReturn(
            $this->getUserEntity($data)
        );

        $user = $this->subject->createUser($data);
        $this->assertCount(count($data['roles']), $user->getRoles());
    }

    /**
     * @throws Exception
     */
    public function testCreateUserWithDefaultRole(): void
    {
        $data = $this->getUser([
            'roles' => [
                [
                    'uuid' => 'uuid',
                    'name' => UserRole::ROLE_USER,
                ],
            ],
        ]);

        $defaultRole = (new UserRole())->setName(UserRole::ROLE_USER);
        $this->userRoleRepository->method('find')->willReturn($defaultRole);
        $this->userRepository->method('saveUser')->willReturn(
            $this->getUserEntity($data)
        );

        $user = $this->subject->createUser($data);

        $this->assertCount(1, $user->getRoles());
        $this->assertSame($defaultRole->getName(), ($user->getRoles()->first())->getName());
    }

    /**
     * @throws Exception
     */
    public function testCreateUser(): void
    {
        $this->userRoleRepository->method('find')->willReturn(new UserRole());
        $this->userRepository->method('saveUser')->willReturn(
            $this->getUserEntity($this->getUser())
        );

        $data = $this->getUser();
        $user = $this->subject->createUser($data);

        $this->assertSame($data['identity'], $user->getIdentity());
        $this->assertTrue(User::verifyPassword($data['password'], $user->getPassword()));
        $this->assertSame($data['detail']['firstName'], $user->getDetail()->getFirstName());
        $this->assertSame($data['detail']['lastName'], $user->getDetail()->getLastName());
        $this->assertSame($data['detail']['email'], $user->getDetail()->getEmail());
        $this->assertSame(UserStatusEnum::Pending, $user->getStatus());
        $this->assertFalse($user->isActive());
    }

    /**
     * @throws NotFoundException
     * @throws ConflictException
     * @throws BadRequestException
     */
    public function testUpdateUserThrowsExceptionDuplicateUserDetailEmail(): void
    {
        $this->userDetailRepository->method('findOneBy')->willReturn($this->getUserEntity()->getDetail());

        $this->expectException(Exception::class);

        $this->subject->updateUser($this->getUserEntity(), [
            'detail' => [
                'email' => 'test@dotkernel.com',
            ],
        ]);
    }

    /**
     * @throws Exception
     */
    public function testUpdateUser(): void
    {
        $user = $this->getUserEntity($this->getUser());

        $this->userRepository->method('saveUser')->willReturn($user);

        $updateData = [
            'identity' => 'test@test.com',
            'password' => '654321',
            'detail'   => [
                'firstName' => 'firstname',
                'lastName'  => 'lastname',
                'email'     => 'email@test.com',
            ],
        ];

        $updatedUser = $this->subject->updateUser($user, $updateData);

        $this->assertTrue(User::verifyPassword($updateData['password'], $updatedUser->getPassword()));
        $this->assertSame($updateData['detail']['firstName'], $updatedUser->getDetail()->getFirstName());
        $this->assertSame($updateData['detail']['lastName'], $updatedUser->getDetail()->getLastName());
        $this->assertSame($updateData['detail']['email'], $updatedUser->getDetail()->getEmail());
    }

    private function getUser(array $data = []): array
    {
        $user = [
            'identity' => 'test@dotkernel.com',
            'password' => 'dotkernel',
            'detail'   => [
                'firstName' => 'first',
                'lastName'  => 'last',
                'email'     => 'test@dotkernel2.com',
            ],
            'roles'    => [
                ['uuid' => 'uuid', 'name' => UserRole::ROLE_USER],
            ],
        ];

        return array_merge($user, $data);
    }

    private function getUserEntity(array $data = []): User
    {
        $user = new User();
        $user
            ->setIdentity($data['identity'] ?? '')
            ->usePassword($data['password'] ?? '')
            ->setStatus($data['status'] ?? UserStatusEnum::Pending)
            ->setDetail(
                (new UserDetail())
                    ->setUser($user)
                    ->setEmail($data['detail']['email'] ?? '')
                    ->setFirstName($data['detail']['firstName'] ?? null)
                    ->setLastName($data['detail']['lastName'] ?? null)
            );

        foreach ($data['roles'] ?? [] as $role) {
            $user->addRole(
                (new UserRole())->setName($role['name'])
            );
        }

        return $user;
    }
}
