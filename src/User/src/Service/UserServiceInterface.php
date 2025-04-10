<?php

declare(strict_types=1);

namespace Api\User\Service;

use Core\App\Exception\BadRequestException;
use Core\App\Exception\ConflictException;
use Core\App\Exception\NotFoundException;
use Core\User\Entity\User;
use Core\User\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;

interface UserServiceInterface
{
    public function getUserRepository(): UserRepository;

    public function activateUser(User $user): User;

    public function deactivateUser(User $user): User;

    public function deleteUser(User $user): User;

    /**
     * @throws NotFoundException
     */
    public function findByEmail(string $email): User;

    public function findByIdentity(string $identity): ?User;

    /**
     * @throws NotFoundException
     */
    public function findOneBy(array $params): User;

    /**
     * @throws NotFoundException
     */
    public function findUser(string $id): User;

    /**
     * @param array<string, mixed> $params
     */
    public function getUsers(array $params): QueryBuilder;

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function saveUser(array $data, ?User $user = null): User;
}
