<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Api\App\Repository\OauthUserRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use Psr\Container\ContainerInterface;

/**
 * Class OauthUserRepositoryFactory
 * @package Api\App\Factory
 */
class OauthUserRepositoryFactory
{
    /**
     * @param ContainerInterface $container
     * @return OauthUserRepository
     */
    public function __invoke(ContainerInterface $container): OauthUserRepository
    {
        return new OauthUserRepository(
            $container->get(PdoService::class)
        );
    }
}
