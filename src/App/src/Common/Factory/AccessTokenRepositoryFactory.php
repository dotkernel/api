<?php

declare(strict_types=1);

namespace Api\App\Common\Factory;

use Api\App\Common\Repository\AccessTokenRepository;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\PdoService;

/**
 * Class AccessTokenRepositoryFactory
 * @package Api\App\Common\Factory
 */
class AccessTokenRepositoryFactory
{
    /**
     * @param ContainerInterface $container
     * @return AccessTokenRepository
     */
    public function __invoke(ContainerInterface $container) : AccessTokenRepository
    {
        return new AccessTokenRepository(
            $container->get(PdoService::class)
        );
    }
}
