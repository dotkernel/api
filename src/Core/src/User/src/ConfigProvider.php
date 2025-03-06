<?php

declare(strict_types=1);

namespace Core\User;

use Core\User\DBAL\Types\UserResetPasswordStatusEnumType;
use Core\User\DBAL\Types\UserStatusEnumType;
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
                'orm_default'  => [
                    'drivers' => [
                        'Core\User\Entity' => 'UserEntities',
                    ],
                ],
                'UserEntities' => [
                    'class' => AttributeDriver::class,
                    'cache' => 'array',
                    'paths' => getcwd() . '/src/Core/src/User/src/Entity',
                ],
            ],
            'types'  => [
                UserStatusEnumType::NAME              => UserStatusEnumType::class,
                UserResetPasswordStatusEnumType::NAME => UserResetPasswordStatusEnumType::class,
            ],
        ];
    }
}
