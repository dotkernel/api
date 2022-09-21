<?php

namespace Api\Fixtures;

use Api\Admin\Entity\AdminRole;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class AdminRoleLoader
 * @package Api\Fixtures
 */
class AdminRoleLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $admin = (new AdminRole())->setName(AdminRole::ROLE_ADMIN);
        $superAdmin = (new AdminRole())->setName(AdminRole::ROLE_SUPERUSER);

        $manager->persist($admin);
        $manager->persist($superAdmin);

        $manager->flush();
    }
}
