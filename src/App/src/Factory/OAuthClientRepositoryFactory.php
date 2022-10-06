<?php


namespace Api\App\Factory;


use Api\App\Entity\OAuthAuthCode;
use Api\App\Entity\OAuthClient;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Psr\Container\ContainerInterface;

class OAuthClientRepositoryFactory
{
    /**
     * @param ContainerInterface $container
     * @return ObjectRepository
     */
    public function __invoke(ContainerInterface $container): ObjectRepository
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        return $entityManager->getRepository(OAuthClient::class);
    }
}
