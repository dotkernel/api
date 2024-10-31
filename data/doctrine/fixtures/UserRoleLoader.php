<?php

declare(strict_types=1);

namespace Api\Fixtures;

use Api\User\Entity\UserRole;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserRoleLoader implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $guest = (new UserRole())->setName(UserRole::ROLE_GUEST);
        $manager->persist($guest);

        $user = (new UserRole())->setName(UserRole::ROLE_USER);
        $manager->persist($user);

        $manager->flush();
    }
}
