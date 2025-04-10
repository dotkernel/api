<?php

declare(strict_types=1);

namespace Core\Setting;

use Core\Setting\DBAL\Types\SettingIdentifierEnumType;
use Core\Setting\Repository\SettingRepository;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Dot\DependencyInjection\Factory\AttributedRepositoryFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'doctrine'     => $this->getDoctrineConfig(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                SettingRepository::class => AttributedRepositoryFactory::class,
            ],
        ];
    }

    public function getDoctrineConfig(): array
    {
        return [
            'driver' => [
                'orm_default'     => [
                    'drivers' => [
                        'Core\Setting\Entity' => 'SettingEntities',
                    ],
                ],
                'SettingEntities' => [
                    'class' => AttributeDriver::class,
                    'cache' => 'array',
                    'paths' => [__DIR__ . '/Entity'],
                ],
            ],
            'types'  => [
                SettingIdentifierEnumType::NAME => SettingIdentifierEnumType::class,
            ],
        ];
    }
}
