<?php

declare(strict_types=1);

namespace Core\App\Fixture;

use Core\Security\Entity\OAuthClient;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

use function password_hash;

use const PASSWORD_DEFAULT;

class OAuthClientLoader extends AbstractFixture implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $oauthClientFrontend = (new OAuthClient())
            ->setName('frontend')
            ->setSecret(password_hash('frontend', PASSWORD_DEFAULT))
            ->setRedirect('/')
            ->setIsConfidential(false)
            ->setIsRevoked(false);
        $manager->persist($oauthClientFrontend);

        $oauthClientAdmin = (new OAuthClient())
            ->setName('admin')
            ->setSecret(password_hash('admin', PASSWORD_DEFAULT))
            ->setRedirect('/')
            ->setIsConfidential(false)
            ->setIsRevoked(false);
        $manager->persist($oauthClientAdmin);

        $manager->flush();
    }
}
