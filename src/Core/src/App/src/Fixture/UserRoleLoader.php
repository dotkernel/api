<?php

declare(strict_types=1);

namespace Core\App\Fixture;

use Core\User\Entity\UserRole;
use Core\User\Enum\UserRoleEnum;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserRoleLoader implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist(
            (new UserRole())->setName(UserRoleEnum::User)
        );
        $manager->persist(
            (new UserRole())->setName(UserRoleEnum::Guest)
        );

        $manager->flush();
    }
}
