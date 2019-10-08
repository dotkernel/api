<?php

declare(strict_types=1);

namespace Api\User\Factory;

use Api\User\Entity\UserEntity;
use Api\User\Repository\UserRepository;
use Api\User\Service\UserRoleService;
use Api\User\Service\UserService;
use Doctrine\ORM\EntityManager;
use Dot\Mail\Service\MailService;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class UserServiceFactory
 * @package Api\User\Factory
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
        $em = $container->get(EntityManager::class);

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository(UserEntity::class);

        return new UserService(
            $userRepository,
            $container->get(UserRoleService::class),
            $container->get(MailService::class),
            $container->get(TemplateRendererInterface::class),
            $container->get('config'));
    }
}
