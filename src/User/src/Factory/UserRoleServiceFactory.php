<?php

declare(strict_types=1);

namespace Api\User\Factory;

use Api\User\Entity\UserRoleEntity;
use Api\User\Repository\UserRoleRepository;
use Api\User\Service\UserRoleService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class RoleServiceFactory
 * @package Api\User\Factory
 */
class UserRoleServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return UserRoleService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UserRoleService
    {
        /** @var EntityManager $em */
        $em = $container->get(EntityManager::class);

        /** @var UserRoleRepository $roleRepository */
        $roleRepository = $em->getRepository(UserRoleEntity::class);

        return new UserRoleService($roleRepository);
    }
}
