<?php

declare(strict_types=1);

namespace App\User\Factory;

use App\User\Entity\UserEntity;
use App\User\Repository\UserRepository;
use App\User\Service\UserService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class UserServiceFactory
 * @package App\User\Factory
 */
class UserServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return UserService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UserService
    {
        /** @var EntityManager $em */
        $em = $container->get('doctrine.entity_manager.orm_default');

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository(UserEntity::class);

        return new UserService(
            $userRepository,
            $container->get('config')
        );
    }
}
