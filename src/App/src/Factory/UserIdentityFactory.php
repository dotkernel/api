<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Api\App\UserIdentity;
use Mezzio\Authentication\UserInterface;
use Psr\Container\ContainerInterface;

class UserIdentityFactory
{
    public function __invoke(ContainerInterface $container): callable
    {
        return function (string $identity, array $roles = [], array $details = []): UserInterface {
            return new UserIdentity($identity, $roles, $details);
        };
    }
}
