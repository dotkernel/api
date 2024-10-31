<?php

declare(strict_types=1);

namespace Api\Fixtures;

use Api\Admin\Entity\AdminRole;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AdminRoleLoader implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $adminRole = (new AdminRole())->setName(AdminRole::ROLE_ADMIN);
        $manager->persist($adminRole);

        $superUserRole = (new AdminRole())->setName(AdminRole::ROLE_SUPERUSER);
        $manager->persist($superUserRole);

        $manager->flush();
    }
}
