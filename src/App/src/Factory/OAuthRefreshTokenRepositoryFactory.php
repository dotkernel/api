<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Api\App\Entity\OAuthRefreshToken;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class OAuthRefreshTokenRepositoryFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ObjectRepository
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        return $entityManager->getRepository(OAuthRefreshToken::class);
    }
}
