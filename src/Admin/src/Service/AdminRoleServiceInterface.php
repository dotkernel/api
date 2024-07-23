<?php

declare(strict_types=1);

namespace Api\Admin\Service;

use Api\Admin\Collection\AdminRoleCollection;
use Api\Admin\Entity\AdminRole;
use Api\App\Exception\BadRequestException;
use Api\App\Exception\NotFoundException;

interface AdminRoleServiceInterface
{
    /**
     * @throws NotFoundException
     */
    public function findOneBy(array $params = []): AdminRole;

    /**
     * @throws BadRequestException
     */
    public function getAdminRoles(array $params = []): AdminRoleCollection;
}
