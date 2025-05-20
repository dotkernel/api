<?php

declare(strict_types=1);

namespace Core\Security;

use Core\Security\Repository\OAuthAccessTokenRepository;
use Core\Security\Repository\OAuthAuthCodeRepository;
use Core\Security\Repository\OAuthClientRepository;
use Core\Security\Repository\OAuthRefreshTokenRepository;
use Core\Security\Repository\OAuthScopeRepository;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Dot\DependencyInjection\Factory\AttributedRepositoryFactory;

/**
 * @phpstan-type ConfigType array{
 *      dependencies: DependenciesType,
 *      doctrine: DoctrineConfigType,
 * }
 * @phpstan-type DoctrineConfigType array{
 *      driver: array{
 *          orm_default: array{
 *              drivers: array<string, string>,
 *          },
 *          SecurityEntities: array{
 *              class: class-string<MappingDriver>,
 *              cache: string,
 *              paths: array<string>,
 *          },
 *      }
 * }
 * @phpstan-type DependenciesType array{
 *       factories: array<class-string, class-string>,
 * }
 */
class ConfigProvider
{
    /**
     * @return ConfigType
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'doctrine'     => $this->getDoctrineConfig(),
        ];
    }

    /**
     * @return DependenciesType
     */
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

    /**
     * @return DoctrineConfigType
     */
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
                    'paths' => [__DIR__ . '/Entity'],
                ],
            ],
        ];
    }
}
