<?php

declare(strict_types=1);

namespace Core\Admin\Service;

use Core\Admin\Entity\Admin;
use Core\Admin\Entity\AdminRole;
use Core\Admin\Enum\AdminStatusEnum;
use Core\Admin\Repository\AdminRepository;
use Core\Admin\Repository\AdminRoleRepository;
use Core\App\Exception\BadRequestException;
use Core\App\Exception\ConflictException;
use Core\App\Exception\NotFoundException;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Ramsey\Uuid\UuidInterface;

class AdminService implements AdminServiceInterface
{
    #[Inject(
        AdminRepository::class,
        AdminRoleRepository::class,
    )]
    public function __construct(
        protected AdminRepository $adminRepository,
        protected AdminRoleRepository $adminRoleRepository,
    ) {
    }

    public function getAdminRepository(): AdminRepository
    {
        return $this->adminRepository;
    }

    /**
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function createAdmin(array $data = []): Admin
    {
        $admin = (new Admin())
            ->setIdentity($data['identity'])
            ->usePassword($data['password'])
            ->setFirstName($data['firstName'])
            ->setLastName($data['lastName'])
            ->setStatus($data['status'] ?? AdminStatusEnum::Active);

        $this->validateUniqueUser($admin->getIdentity());

        foreach ($data['roles'] as $roleData) {
            $adminRole = $this->adminRoleRepository->find($roleData['uuid']);
            if (! $adminRole instanceof AdminRole) {
                throw new NotFoundException(Message::ROLE_NOT_FOUND);
            }
            $admin->addRole($adminRole);
        }

        return $this->adminRepository->saveAdmin($admin);
    }

    public function deleteAdmin(Admin $admin): void
    {
        $this->adminRepository->deleteAdmin($admin);
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function updateAdmin(Admin $admin, array $data = []): Admin
    {
        if (! empty($data['password'])) {
            $admin->usePassword($data['password']);
        }

        if (isset($data['firstName'])) {
            $admin->setFirstName($data['firstName']);
        }

        if (isset($data['lastName'])) {
            $admin->setLastName($data['lastName']);
        }

        if (isset($data['status'])) {
            $admin->setStatus($data['status']);
        }

        $this->validateUniqueUser($admin->getIdentity(), $admin->getUuid());

        if (! empty($data['roles'])) {
            $admin->resetRoles();
            foreach ($data['roles'] as $roleData) {
                $adminRole = $this->adminRoleRepository->find($roleData['uuid']);
                if (! $adminRole instanceof AdminRole) {
                    throw new NotFoundException(Message::ROLE_NOT_FOUND);
                }
                $admin->addRole($adminRole);
            }
        }

        if (! $admin->hasRoles()) {
            throw (new BadRequestException())->setMessages([Message::RESTRICTION_ROLES]);
        }

        return $this->adminRepository->saveAdmin($admin);
    }

    /**
     * @throws ConflictException
     */
    public function validateUniqueUser(string $identity, ?UuidInterface $uuid = null): void
    {
        $admin = $this->adminRepository->findOneBy(['identity' => $identity]);
        if ($admin instanceof Admin) {
            if ($uuid === null) {
                throw new ConflictException(Message::DUPLICATE_IDENTITY);
            }
            if ($admin->getUuid()->toString() !== $uuid->toString()) {
                throw new ConflictException(Message::DUPLICATE_IDENTITY);
            }
        }
    }
}
