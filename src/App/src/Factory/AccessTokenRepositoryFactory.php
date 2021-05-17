<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Api\App\Repository\AccessTokenRepository;
use Psr\Container\ContainerInterface;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;

/**
 * Class AccessTokenRepositoryFactory
 * @package Api\App\Factory
 */
class AccessTokenRepositoryFactory
{
    /**
     * @param ContainerInterface $container
     * @return AccessTokenRepository
     */
    public function __invoke(ContainerInterface $container): AccessTokenRepository
    {
        return new AccessTokenRepository(
            $container->get(PdoService::class)
        );
    }
}
