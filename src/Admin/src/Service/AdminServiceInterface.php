<?php

declare(strict_types=1);

namespace Api\Admin\Service;

use Api\Admin\Collection\AdminCollection;
use Api\Admin\Entity\Admin;
use Exception;

interface AdminServiceInterface
{
    /**
     * @throws Exception
     */
    public function createAdmin(array $data = []): Admin;

    /**
     * @throws Exception
     */
    public function deleteAdmin(Admin $admin): void;

    public function exists(string $identity = ''): bool;

    public function findOneBy(array $params = []): ?Admin;

    public function getAdmins(array $params = []): AdminCollection;

    /**
     * @throws Exception
     */
    public function updateAdmin(Admin $admin, array $data = []): Admin;
}
