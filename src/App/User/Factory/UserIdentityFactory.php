<?php

declare(strict_types=1);

namespace App\User\Factory;

use App\User\Entity\UserIdentity;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Authentication\UserInterface;

/**
 * Class UserIdentityFactory
 * @package App\User
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
