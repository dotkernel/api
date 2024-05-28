<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\App\Exception\ConflictException;
use Api\User\Collection\UserCollection;
use Api\User\Entity\User;
use Api\User\Entity\UserResetPasswordEntity;
use Dot\Mail\Exception\MailException;
use RuntimeException;

interface UserServiceInterface
{
    /**
     * @throws RuntimeException
     */
    public function activateUser(User $user): User;

    /**
     * @throws ConflictException
     * @throws RuntimeException
     */
    public function createUser(array $data = []): User;

    public function revokeTokens(User $user): void;

    /**
     * @throws RuntimeException
     */
    public function deleteUser(User $user): User;

    /**
     * @throws RuntimeException
     */
    public function anonymizeUser(User $user): User;

    public function exists(string $identity = ''): bool;

    public function existsOther(string $identity = '', string $uuid = ''): bool;

    public function emailExists(string $email = ''): bool;

    public function emailExistsOther(string $email = '', string $uuid = ''): bool;

    public function findResetPasswordByHash(?string $hash): ?UserResetPasswordEntity;

    public function findByEmail(string $email): ?User;

    public function findByIdentity(string $identity): ?User;

    public function findOneBy(array $params = []): ?User;

    public function getUsers(array $params = []): UserCollection;

    /**
     * @throws MailException
     */
    public function sendActivationMail(User $user): bool;

    /**
     * @throws MailException
     */
    public function sendResetPasswordRequestedMail(User $user): bool;

    /**
     * @throws MailException
     */
    public function sendResetPasswordCompletedMail(User $user): bool;

    /**
     * @throws MailException
     */
    public function sendWelcomeMail(User $user): bool;

    /**
     * @throws RuntimeException
     */
    public function updateUser(User $user, array $data = []): User;

    /**
     * @throws MailException
     */
    public function sendRecoverIdentityMail(User $user): bool;
}
