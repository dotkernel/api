<?php

declare(strict_types=1);

namespace Api\Fixtures;

use Api\Admin\Entity\Admin;
use Api\Admin\Entity\AdminRole;
use Api\User\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class AdminLoader
 * @package Api\Fixtures
 */
class AdminLoader implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $adminRoleRepository = $manager->getRepository(AdminRole::class);

        /** @var AdminRole $adminRole */
        $adminRole = $adminRoleRepository->findOneBy([
            'name' => AdminRole::ROLE_ADMIN,
        ]);

        /** @var AdminRole $superUserRole */
        $superUserRole = $adminRoleRepository->findOneBy([
            'name' => AdminRole::ROLE_SUPERUSER,
        ]);

        $admin = (new Admin())
            ->setIdentity('admin')
            ->usePassword('dotkernel')
            ->setFirstName('DotKernel')
            ->setLastName('Admin')
            ->setStatus(User::STATUS_ACTIVE)
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
