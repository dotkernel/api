<?php

namespace Api\Fixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class OAuthScopeLoader
 * @package Api\Fixtures
 */
class OAuthScopeLoader implements FixtureInterface
{
    private const TABLE_NAME = 'oauth_scopes';

    public function load(ObjectManager $manager)
    {
        $manager->getConnection()->insert(self::TABLE_NAME, [
            'id' => 'api'
        ]);
    }
}
