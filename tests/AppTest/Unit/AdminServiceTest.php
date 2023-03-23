<?php

declare(strict_types=1);

namespace AppTest\Unit;

use Api\Admin\Entity\Admin;
use Api\Admin\Entity\AdminRole;
use Api\Admin\Repository\AdminRepository;
use Api\Admin\Service\AdminRoleService;
use Api\Admin\Service\AdminService as Subject;
use Api\App\Message;
use Exception;
use PHPUnit\Framework\TestCase;

class AdminServiceTest extends TestCase
{
    private Subject $subject;
    private AdminRoleService $adminRoleService;
    private AdminRepository $adminRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->adminRoleService = $this->createMock(AdminRoleService::class);
        $this->adminRepository = $this->createMock(AdminRepository::class);

        $this->subject = $this->getMockBuilder(Subject::class)
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
    public function testCreateAdminThrowsDuplicateIdentity()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(Message::DUPLICATE_IDENTITY);

        $this->subject->method('exists')->willReturn(true);

        $this->subject->createAdmin(['identity' => 'admin@dotkernel.com']);
    }

    public function testCreateAdminSuperAdminRole()
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
        $this->assertSame(Admin::STATUS_ACTIVE, $admin->getStatus());
    }

    private function getAdmin(array $data = []): array
    {
        $admin = [
            'identity' => 'admin@dotkernel.com',
            'password' => 'dotkernel',
            'firstName' => 'firstname',
            'lastName' => 'lastname',
        ];

        return array_merge($admin, $data);
    }

    private function getAdminEntity(array $data = []): Admin
    {
        $admin = (new Admin())
            ->setIdentity($data['identity'] ?? null)
            ->usePassword($data['password'] ?? '')
            ->setFirstName($data['firstName'] ?? null)
            ->setLastName($data['lastName'] ?? null)
            ->setStatus($data['status'] ?? Admin::STATUS_ACTIVE);

        foreach ($data['roles'] ?? [] as $role) {
            $admin->addRole(
                (new AdminRole())->setName($role['name'])
            );
        }

        return $admin;
    }
}
