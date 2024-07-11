<?php

declare(strict_types=1);

namespace Api\Admin\Service;

use Api\Admin\Collection\AdminCollection;
use Api\Admin\Entity\Admin;
use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;

interface AdminServiceInterface
{
    /**
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function createAdmin(array $data = []): Admin;

    public function deleteAdmin(Admin $admin): void;

    public function exists(string $identity = ''): bool;

    /**
     * @throws NotFoundException
     */
    public function findOneBy(array $params = []): Admin;

    /**
     * @throws BadRequestException
     */
    public function getAdmins(array $params = []): AdminCollection;

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function updateAdmin(Admin $admin, array $data = []): Admin;
}
