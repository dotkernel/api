<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\NotFoundException;
use Api\User\Collection\UserRoleCollection;
use Api\User\Entity\UserRole;

interface UserRoleServiceInterface
{
    /**
     * @throws NotFoundException
     */
    public function findOneBy(array $params = []): UserRole;

    /**
     * @throws BadRequestException
     */
    public function getRoles(array $params = []): UserRoleCollection;
}
