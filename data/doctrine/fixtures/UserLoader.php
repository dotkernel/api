<?php

declare(strict_types=1);

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
    public function load(ObjectManager $manager): void
    {
        $userRoleRepository = $manager->getRepository(UserRole::class);

        /** @var UserRole $guestRole */
        $guestRole = $userRoleRepository->findOneBy([
            'name' => UserRole::ROLE_GUEST,
        ]);

        /** @var UserRole $userRole */
        $userRole = $userRoleRepository->findOneBy([
            'name' => UserRole::ROLE_USER,
        ]);

        $user = (new User())
            ->setIdentity('test@dotkernel.com')
            ->usePassword('dotkernel')
            ->setStatus(User::STATUS_ACTIVE)
            ->setIsDeleted(false)
            ->setHash(User::generateHash())
            ->addRole($guestRole)
            ->addRole($userRole);
        $manager->persist($user);

        $userDetail = (new UserDetail())
            ->setUser($user)
            ->setFirstName('Test')
            ->setLastName('Account')
            ->setEmail('test@dotkernel.com');
        $manager->persist($userDetail);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserRoleLoader::class,
        ];
    }
}
