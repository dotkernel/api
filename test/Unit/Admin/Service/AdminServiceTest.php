<?php

declare(strict_types=1);

namespace ApiTest\Unit\Admin\Service;

use Api\Admin\Service\AdminService;
use Api\Admin\Service\AdminServiceInterface;
use Core\Admin\Entity\Admin;
use Core\Admin\Entity\AdminRole;
use Core\Admin\Enum\AdminRoleEnum;
use Core\Admin\Enum\AdminStatusEnum;
use Core\Admin\Repository\AdminRepository;
use Core\Admin\Repository\AdminRoleRepository;
use Core\App\Exception\ConflictException;
use Core\App\Message;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function count;

class AdminServiceTest extends TestCase
{
    private AdminServiceInterface $adminService;
    private AdminRepository&MockObject $adminRepository;
    private AdminRoleRepository&MockObject $adminRoleRepository;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function setUp(): void
    {
        $this->adminRepository     = $this->createMock(AdminRepository::class);
        $this->adminRoleRepository = $this->createMock(AdminRoleRepository::class);
        $this->adminService        = new AdminService($this->adminRepository, $this->adminRoleRepository);
    }

    /**
     * @throws Exception
     */
    public function testCreateAdminThrowsDuplicateIdentity(): void
    {
        $this->expectException(ConflictException::class);
        $this->expectExceptionMessage(Message::DUPLICATE_IDENTITY);

        $request = $this->getAdmin();
        $this->adminRepository
            ->method('findOneBy')
            ->willReturn($this->getAdminEntity(['identity' => $request['identity']]));

        $this->adminService->saveAdmin($request);
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
                    'name' => AdminRoleEnum::Superuser,
                ],
            ],
        ]);

        $role = (new AdminRole())->setName(AdminRoleEnum::Superuser);

        $this->adminRoleRepository->method('find')->willReturn($role);

        $admin = $this->adminService->saveAdmin($data);

        $this->assertSame($data['identity'], $admin->getIdentity());
        $this->assertTrue($admin->verifyPassword($data['password']));
        $this->assertCount(count($data['roles']), $admin->getRoles());
        $this->assertSame($role->getName(), ($admin->getRoles()[0])->getName());
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
