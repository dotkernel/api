<?php

namespace Api\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DateTimeImmutable;

/**
 * Class OAuthClientLoader
 * @package Api\Fixtures
 */
class OAuthClientLoader extends AbstractFixture implements FixtureInterface
{
    private const TABLE_NAME = 'oauth_clients';

    public function load(ObjectManager $manager)
    {
        $manager->getConnection()->insert(self::TABLE_NAME, [
            'name' => 'frontend',
            'user_id' => null,
            'secret' => password_hash('frontend', PASSWORD_DEFAULT),
            'redirect' => '/',
            'isConfidential' => 0,
            'revoked' => 0,
        ]);

        $manager->getConnection()->insert(self::TABLE_NAME, [
            'name' => 'admin',
            'user_id' => null,
            'secret' => password_hash('admin', PASSWORD_DEFAULT),
            'redirect' => '/',
            'isConfidential' => 0,
            'revoked' => 0,
        ]);
    }
}
