<?php

declare(strict_types=1);

namespace Api\Fixtures;

use Api\Admin\Entity\Admin;
use Api\Admin\Entity\AdminRole;
use Api\Admin\Enum\AdminStatusEnum;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

use function assert;

class AdminLoader implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $adminRoleRepository = $manager->getRepository(AdminRole::class);

        $adminRole = $adminRoleRepository->findOneBy([
            'name' => AdminRole::ROLE_ADMIN,
        ]);
        assert($adminRole instanceof AdminRole);

        $superUserRole = $adminRoleRepository->findOneBy([
            'name' => AdminRole::ROLE_SUPERUSER,
        ]);
        assert($superUserRole instanceof AdminRole);

        $admin = (new Admin())
            ->setIdentity('admin')
            ->usePassword('dotkernel')
            ->setFirstName('DotKernel')
            ->setLastName('Admin')
            ->setStatus(AdminStatusEnum::Active)
            ->addRole($adminRole)
            ->addRole($superUserRole);

        $manager->persist($admin);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AdminRoleLoader::class,
        ];
    }
}
