<?php

declare(strict_types=1);

namespace Api\Admin\Service;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Core\Admin\Entity\Admin;
use Core\Admin\Repository\AdminRepository;
use Doctrine\ORM\QueryBuilder;

interface AdminServiceInterface
{
    public function getAdminRepository(): AdminRepository;

    public function deleteAdmin(Admin $admin): void;

    /**
     * @throws NotFoundException
     */
    public function findAdmin(string $id): Admin;

    /**
     * @param array<non-empty-string, mixed> $params
     */
    public function getAdmins(array $params): QueryBuilder;

    /**
     * @param non-empty-array<non-empty-string, mixed> $data
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function saveAdmin(array $data, ?Admin $admin = null): Admin;
}
