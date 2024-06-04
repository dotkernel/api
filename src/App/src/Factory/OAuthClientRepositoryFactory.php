<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Api\App\Entity\OAuthClient;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function assert;

class OAuthClientRepositoryFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ObjectRepository
    {
        $entityManager = $container->get(EntityManagerInterface::class);
        assert($entityManager instanceof EntityManagerInterface);

        return $entityManager->getRepository(OAuthClient::class);
    }
}
