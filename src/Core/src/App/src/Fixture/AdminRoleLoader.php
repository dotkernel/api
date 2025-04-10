<?php

declare(strict_types=1);

namespace Core\App\Fixture;

use Core\Admin\Entity\AdminRole;
use Core\Admin\Enum\AdminRoleEnum;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AdminRoleLoader implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist(
            (new AdminRole())->setName(AdminRoleEnum::Superuser)
        );
        $manager->persist(
            (new AdminRole())->setName(AdminRoleEnum::Admin)
        );

        $manager->flush();
    }
}
