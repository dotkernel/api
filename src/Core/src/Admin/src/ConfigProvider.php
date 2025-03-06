<?php

declare(strict_types=1);

namespace Core\Admin;

use Core\Admin\DBAL\Types\AdminStatusEnumType;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;

use function getcwd;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'doctrine' => $this->getDoctrineConfig(),
        ];
    }

    private function getDoctrineConfig(): array
    {
        return [
            'driver' => [
                'orm_default'   => [
                    'drivers' => [
                        'Core\Admin\Entity' => 'AdminEntities',
                    ],
                ],
                'AdminEntities' => [
                    'class' => AttributeDriver::class,
                    'cache' => 'array',
                    'paths' => getcwd() . '/src/Core/src/Admin/src/Entity',
                ],
            ],
            'types'  => [
                AdminStatusEnumType::NAME => AdminStatusEnumType::class,
            ],
        ];
    }
}
