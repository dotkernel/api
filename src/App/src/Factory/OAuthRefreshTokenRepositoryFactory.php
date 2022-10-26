<?php


namespace Api\App\Factory;


use Api\App\Entity\OAuthClient;
use Api\App\Entity\OAuthRefreshToken;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Psr\Container\ContainerInterface;

class OAuthRefreshTokenRepositoryFactory
{
    /**
     * @param ContainerInterface $container
     * @return ObjectRepository
     */
    public function __invoke(ContainerInterface $container): ObjectRepository
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        return $entityManager->getRepository(OAuthRefreshToken::class);
    }
}
