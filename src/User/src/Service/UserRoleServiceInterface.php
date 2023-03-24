<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\User\Collection\UserRoleCollection;
use Api\User\Entity\UserRole;

interface UserRoleServiceInterface
{
    public function findOneBy(array $params = []): ?UserRole;

    public function getRoles(array $params = []): UserRoleCollection;
}
