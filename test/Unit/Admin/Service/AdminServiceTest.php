<?php

declare(strict_types=1);

namespace ApiTest\Unit\Admin\Service;

use Api\Admin\Entity\Admin;
use Api\Admin\Entity\AdminRole;
use Api\Admin\Enum\AdminStatusEnum;
use Api\Admin\Repository\AdminRepository;
use Api\Admin\Service\AdminRoleService;
use Api\Admin\Service\AdminService as Subject;
use Api\App\Message;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function count;

class AdminServiceTest extends TestCase
{
    private Subject|MockObject $subject;
    private AdminRoleService|MockObject $adminRoleService;
    private AdminRepository|MockObject $adminRepository;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function setUp(): void
    {
        $this->adminRoleService = $this->createMock(AdminRoleService::class);
        $this->adminRepository  = $this->createMock(AdminRepository::class);
        $this->subject          = $this->getMockBuilder(Subject::class)
            ->setConstructorArgs([
                $this->adminRoleService,
                $this->adminRepository,
            ])
            ->onlyMethods([
                'exists',
            ])
            ->getMock();
    }

    /**
     * @throws Exception
     */
    public function testCreateAdminThrowsDuplicateIdentity(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(Message::DUPLICATE_IDENTITY);

        $this->subject->method('exists')->willReturn(true);

        $this->subject->createAdmin(['identity' => 'admin@dotkernel.com']);
    }

    /**
     * @throws Exception
     */
    public function testCreateAdminSuperAdminRole(): void
    {
        $data = $this->getAdmin([
            'roles' => [
                [
                    'uuid' => 'uuid',
                    'name' => AdminRole::ROLE_SUPERUSER,
                ],
            ],
        ]);

        $role = (new AdminRole())->setName(AdminRole::ROLE_SUPERUSER);

        $this->adminRoleService->method('findOneBy')->willReturn($role);
        $this->adminRepository->method('saveAdmin')->willReturn(
            $this->getAdminEntity($data)
        );

        $admin = $this->subject->createAdmin($data);

        $this->assertSame($data['identity'], $admin->getIdentity());
        $this->assertTrue(Admin::verifyPassword($data['password'], $admin->getPassword()));
        $this->assertCount(count($data['roles']), $admin->getRoles());
        $this->assertSame($role->getName(), ($admin->getRoles()->first())->getName());
        $this->assertSame(AdminStatusEnum::Active, $admin->getStatus());
    }

    private function getAdmin(array $data = []): array
    {
        $admin = [
            'identity'  => 'admin@dotkernel.com',
            'password'  => 'dotkernel',
            'firstName' => 'firstname',
            'lastName'  => 'lastname',
        ];

        return array_merge($admin, $data);
    }

    private function getAdminEntity(array $data = []): Admin
    {
        $admin = (new Admin())
            ->setIdentity($data['identity'] ?? '')
            ->usePassword($data['password'] ?? '')
            ->setFirstName($data['firstName'] ?? '')
            ->setLastName($data['lastName'] ?? '')
            ->setStatus($data['status'] ?? AdminStatusEnum::Active);

        foreach ($data['roles'] ?? [] as $role) {
            $admin->addRole(
                (new AdminRole())->setName($role['name'])
            );
        }

        return $admin;
    }
}
