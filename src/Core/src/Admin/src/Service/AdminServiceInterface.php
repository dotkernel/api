<?php

declare(strict_types=1);

namespace Core\Admin\Service;

use Core\Admin\Entity\Admin;
use Core\Admin\Repository\AdminRepository;
use Core\App\Exception\BadRequestException;
use Core\App\Exception\ConflictException;
use Core\App\Exception\NotFoundException;

interface AdminServiceInterface
{
    public function getAdminRepository(): AdminRepository;

    /**
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function createAdmin(array $data = []): Admin;

    public function deleteAdmin(Admin $admin): void;

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function updateAdmin(Admin $admin, array $data = []): Admin;
}
