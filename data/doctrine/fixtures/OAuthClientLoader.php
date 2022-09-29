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
//        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
//        $manager->getConnection()->insert(self::TABLE_NAME, [
//            'name' => 'frontend',
//            'user_id' => null,
//            'secret' => password_hash('frontend', PASSWORD_DEFAULT),
//            'redirect' => '/',
//            'personal_access_client' => true,
//            'password_client' => true,
//            'revoked' => 0,
//            'created_at' => $now
//        ]);
//
//        $manager->getConnection()->insert(self::TABLE_NAME, [
//            'name' => 'admin',
//            'user_id' => null,
//            'secret' => password_hash('admin', PASSWORD_DEFAULT),
//            'redirect' => '/',
//            'personal_access_client' => true,
//            'password_client' => true,
//            'revoked' => 0,
//            'created_at' => $now
//        ]);
    }
}
