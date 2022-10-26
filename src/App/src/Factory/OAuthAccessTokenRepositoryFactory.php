<?php


namespace Api\App\Factory;


use Api\App\Entity\OAuthAccessToken;
use Api\App\Repository\OAuthAccessTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Psr\Container\ContainerInterface;

class OAuthAccessTokenRepositoryFactory
{
    /**
     * @param ContainerInterface $container
     * @return ObjectRepository
     */
    public function __invoke(ContainerInterface $container): ObjectRepository
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        return $entityManager->getRepository(OAuthAccessToken::class);
    }
}
