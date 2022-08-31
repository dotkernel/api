<?php


namespace Api\Fixtures;


use Api\User\Entity\UserRole;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class UserRoleLoader
 * @package Api\Fixtures
 */
class UserRoleLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $guest = (new UserRole())->setName(UserRole::ROLE_GUEST);
        $user = (new UserRole())->setName(UserRole::ROLE_USER);

        $manager->persist($guest);
        $manager->persist($user);

        $manager->flush();
    }
}
