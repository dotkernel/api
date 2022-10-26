<?php

namespace AppTest\Unit;

use Api\Admin\Entity\Admin;
use Api\Admin\Entity\AdminRole;
use Api\Admin\Repository\AdminRepository;
use Api\Admin\Service\AdminRoleService;
use Api\Admin\Service\AdminService as Subject;
use Api\App\Message;
use Doctrine\ORM\ORMException;
use PHPUnit\Framework\TestCase;
use Exception;

/**
 * Class AdminServiceTest
 * @package Unit
 */
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

    public function testCreateAdminThrowsDuplicateIdentity()
    {
        $this->expectException(ORMException::class);
        $this->expectExceptionMessage(Message::DUPLICATE_IDENTITY);

        $this->subject->method('exists')->willReturn(true);

        $this->subject->createAdmin(['identity' => 'admin@dotkernel.com']);
    }

    public function testCreateAdminThrowsRestrictionRoles()
    {
        $this->adminRoleService->method('findOneBy')->willReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(Message::RESTRICTION_ROLES);

        $admin = $this->getAdmin();

        $this->subject->createAdmin($admin);
    }

    public function testCreateAdminSuperAdminRole()
    {
        $data = $this->getAdmin([
            'roles' => [
                [
                    'uuid' => 'uuid'
                ],
            ],
        ]);

        $role = new AdminRole();
        $role->setName(AdminRole::ROLE_SUPERUSER);

        $this->adminRoleService->method('findOneBy')->willReturn($role);

        $admin = $this->subject->createAdmin($data);

        $this->assertSame($data['identity'], $admin->getIdentity());
        $this->assertTrue(password_verify($data['password'], $admin->getPassword()));
        $this->assertCount(count($data['roles']), $admin->getRoles());
        $this->assertSame($role->getName(), ($admin->getRoles()->first())->getName());
        $this->assertSame(Admin::STATUS_ACTIVE, $admin->getStatus());
    }

    public function createAdminWithAdminRole()
    {
        $data = $this->getAdmin();

        $role = new AdminRole();
        $role->setName(AdminRole::ROLE_ADMIN);

        $this->adminRoleService->method('findOneBy')->willReturn($role);

        $admin = $this->subject->createAdmin($data);

        $this->assertSame($data['identity'], $admin->getIdentity());
        $this->assertTrue(password_verify($data['password'], $admin->getPassword()));
        $this->assertCount(count($data['roles']), $admin->getRoles());
        $this->assertSame($role->getName(), ($admin->getRoles()->first())->getName());
        $this->assertSame(Admin::STATUS_ACTIVE, $admin->getStatus());
    }

    public function updateAdminThrowsRolesRestriction()
    {
        $admin = new Admin();
        $data = $this->getAdmin([
            'roles' => [
                [
                    'uuid' => 'uuid'
                ],
            ],
        ]);

        $this->adminRoleService->method('findOneBy')->willReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(Message::RESTRICTION_ROLES);

        $this->subject->updateAdmin($admin, $data);
    }

    public function updateAdmin()
    {
        $admin = new Admin();

        $data = $this->getAdmin([
            'password' => '654321',
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'roles' => [
                [
                    'uuid' => 'uuid'
                ],
            ],
        ]);

        $role = new AdminRole();
        $role->setName(AdminRole::ROLE_SUPERUSER);

        $this->adminRoleService->method('findOneBy')->willReturn($role);

        $admin = $this->subject->updateAdmin($admin, $data);

        $this->assertTrue(password_verify($data['password'], $admin->getPassword()));
        $this->assertSame($data['firstName'], $admin->getFirstName());
        $this->assertSame($data['lastName'], $admin->getLastName());
        $this->assertCount(count($data['roles']), $admin->getRoles());
        $this->assertSame($role->getName(), ($admin->getRoles()->first())->getName());
    }

    private function getAdmin(array $data = []): array
    {
        $admin = [
            'identity' => 'admin@dotkernel.com',
            'password' => '123456',
        ];

        return array_merge($admin, $data);
    }
}
