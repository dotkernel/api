<?php

declare(strict_types=1);

namespace Core\App\Fixture;

use Core\Admin\Entity\Admin;
use Core\Admin\Entity\AdminRole;
use Core\Admin\Enum\AdminRoleEnum;
use Core\Admin\Enum\AdminStatusEnum;
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
            'name' => AdminRoleEnum::Admin,
        ]);
        assert($adminRole instanceof AdminRole);

        $superUserRole = $adminRoleRepository->findOneBy([
            'name' => AdminRoleEnum::Superuser,
        ]);
        assert($superUserRole instanceof AdminRole);

        $admin = (new Admin())
            ->setIdentity('admin')
            ->usePassword('dotadmin')
            ->setFirstName('Dotkernel')
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
