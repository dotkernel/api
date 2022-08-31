<?php

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
    public function load(ObjectManager $manager)
    {
        $admin = new Admin();
        $admin
            ->setIdentity('admin')
            ->setPassword(password_hash('dotadmin', PASSWORD_DEFAULT))
            ->setFirstName('DotKernel')
            ->setLastName('Admin')
            ->setStatus(User::STATUS_ACTIVE);

        $adminRoleRepository = $manager->getRepository(AdminRole::class);

        /** @var AdminRole $adminRole */
        $adminRole = $adminRoleRepository->findOneBy(['name' => AdminRole::ROLE_ADMIN]);

        /** @var AdminRole $superUserRole */
        $superUserRole = $adminRoleRepository->findOneBy(['name' => AdminRole::ROLE_SUPERUSER]);

        $admin->addRole($adminRole);
        $admin->addRole($superUserRole);

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
