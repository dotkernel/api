<?php

declare(strict_types=1);

namespace Api\Admin\Service;

use Api\Admin\Collection\AdminRoleCollection;
use Api\Admin\Entity\AdminRole;

interface AdminRoleServiceInterface
{
    public function findOneBy(array $params = []): ?AdminRole;

    public function getAdminRoles(array $params = []): AdminRoleCollection;
}
