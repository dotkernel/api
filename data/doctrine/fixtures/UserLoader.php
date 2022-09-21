<?php

namespace Api\Fixtures;

use Api\User\Entity\User;
use Api\User\Entity\UserDetail;
use Api\User\Entity\UserRole;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class UserLoader
 * @package Api\Fixtures
 */
class UserLoader implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user
            ->setIdentity('test@dotkernel.com')
            ->setPassword(password_hash('dotkernel', PASSWORD_DEFAULT))
            ->setStatus(User::STATUS_ACTIVE)
            ->setIsDeleted(false)
            ->setHash(User::generateHash());

        $userDetail = new UserDetail();
        $userDetail
            ->setUser($user)
            ->setFirstName('Test')
            ->setLastName('Account');

        $userRoleRepository = $manager->getRepository(UserRole::class);

        /** @var UserRole $guestRole */
        $guestRole = $userRoleRepository->findOneBy([
            'name' => UserRole::ROLE_GUEST,
        ]);

        /** @var UserRole $userRole */
        $userRole = $userRoleRepository->findOneBy([
            'name' => UserRole::ROLE_USER,
        ]);

        $user->addRole($guestRole);
        $user->addRole($userRole);

        $manager->persist($userDetail);
        $manager->persist($user);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserRoleLoader::class,
        ];
    }
}
