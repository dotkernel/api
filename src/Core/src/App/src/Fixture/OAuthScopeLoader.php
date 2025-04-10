<?php

declare(strict_types=1);

namespace Core\App\Fixture;

use Core\Security\Entity\OAuthScope;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OAuthScopeLoader implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist(
            (new OAuthScope())->setScope('api')
        );

        $manager->flush();
    }
}
