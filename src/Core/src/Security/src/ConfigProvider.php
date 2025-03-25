<?php

declare(strict_types=1);

namespace Core\Security;

use Core\Security\Repository\OAuthAccessTokenRepository;
use Core\Security\Repository\OAuthAuthCodeRepository;
use Core\Security\Repository\OAuthClientRepository;
use Core\Security\Repository\OAuthRefreshTokenRepository;
use Core\Security\Repository\OAuthScopeRepository;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Dot\DependencyInjection\Factory\AttributedRepositoryFactory;

use function getcwd;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'doctrine'     => $this->getDoctrineConfig(),
        ];
    }

    private function getDependencies(): array
    {
        return [
            'factories' => [
                OAuthAccessTokenRepository::class  => AttributedRepositoryFactory::class,
                OAuthAuthCodeRepository::class     => AttributedRepositoryFactory::class,
                OAuthClientRepository::class       => AttributedRepositoryFactory::class,
                OAuthRefreshTokenRepository::class => AttributedRepositoryFactory::class,
                OAuthScopeRepository::class        => AttributedRepositoryFactory::class,
            ],
        ];
    }

    private function getDoctrineConfig(): array
    {
        return [
            'driver' => [
                'orm_default'      => [
                    'drivers' => [
                        'Core\Security\Entity' => 'SecurityEntities',
                    ],
                ],
                'SecurityEntities' => [
                    'class' => AttributeDriver::class,
                    'cache' => 'array',
                    'paths' => getcwd() . '/src/Core/src/Security/src/Entity',
                ],
            ],
        ];
    }
}
