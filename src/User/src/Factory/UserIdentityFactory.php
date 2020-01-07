<?php

declare(strict_types=1);

namespace Api\User\Factory;

use Api\User\Entity\UserIdentity;
use Psr\Container\ContainerInterface;
use Mezzio\Authentication\UserInterface;

/**
 * Class UserIdentityFactory
 * @package Api\User\Factory
 */
class UserIdentityFactory
{
    /**
     * @param ContainerInterface $container
     * @return callable
     */
    public function __invoke(ContainerInterface $container) : callable
    {
        return function (string $identity, array $roles = [], array $details = []) : UserInterface {
            return new UserIdentity($identity, $roles, $details);
        };
    }
}
