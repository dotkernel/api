<?php

namespace Api\Fixtures;

use Api\App\Entity\OAuthScope;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class OAuthScopeLoader
 * @package Api\Fixtures
 */
class OAuthScopeLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $scope = new OAuthScope();
        $scope->setScope('api');

        $manager->persist($scope);
        $manager->flush();
    }
}
