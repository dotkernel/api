<?php

declare(strict_types=1);

namespace Api\Admin\Service;

use Core\Admin\Entity\Admin;
use Core\Admin\Repository\AdminRepository;
use Core\App\Exception\BadRequestException;
use Core\App\Exception\ConflictException;
use Core\App\Exception\NotFoundException;
use Doctrine\ORM\QueryBuilder;

interface AdminServiceInterface
{
    public function getAdminRepository(): AdminRepository;

    public function deleteAdmin(Admin $admin): void;

    /**
     * @throws NotFoundException
     */
    public function findAdmin(string $uuid): Admin;

    /**
     * @param array<string, mixed> $params
     */
    public function getAdmins(array $params): QueryBuilder;

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function saveAdmin(array $data, ?Admin $admin = null): Admin;
}
