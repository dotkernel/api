<?php

namespace Api\Fixtures;

use Api\App\Entity\OAuthClient;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class OAuthClientLoader
 * @package Api\Fixtures
 */
class OAuthClientLoader extends AbstractFixture implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $oauthClientFrontend = new OAuthClient();
        $oauthClientFrontend->setName('frontend');
        $oauthClientFrontend->setSecret(password_hash('frontend', PASSWORD_DEFAULT));
        $oauthClientFrontend->setRedirect('/');
        $oauthClientFrontend->setIsConfidential(false);
        $oauthClientFrontend->setIsRevoked(false);

        $oauthClientAdmin = new OAuthClient();
        $oauthClientAdmin->setName('admin');
        $oauthClientAdmin->setSecret(password_hash('admin', PASSWORD_DEFAULT));
        $oauthClientAdmin->setRedirect('/');
        $oauthClientAdmin->setIsConfidential(false);
        $oauthClientAdmin->setIsRevoked(false);

        $manager->persist($oauthClientFrontend);
        $manager->persist($oauthClientAdmin);
        $manager->flush();
    }
}
