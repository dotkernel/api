<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\User\Collection\UserCollection;
use Api\User\Entity\User;
use Dot\Mail\Exception\MailException;
use Exception;

interface UserServiceInterface
{
    /**
     * @throws Exception
     */
    public function activateUser(User $user): User;

    /**
     * @throws Exception
     */
    public function createUser(array $data = []): User;

    public function revokeTokens(User $user): void;

    /**
     * @throws Exception
     */
    public function deleteUser(User $user): User;

    /**
     * @throws Exception
     */
    public function anonymizeUser(User $user): User;

    public function exists(string $identity = ''): bool;

    public function existsOther(string $identity = '', string $uuid = ''): bool;

    public function emailExists(string $email = ''): bool;

    public function emailExistsOther(string $email = '', string $uuid = ''): bool;

    public function findByResetPasswordHash(?string $hash): ?User;

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
     * @throws Exception
     */
    public function updateUser(User $user, array $data = []): User;

    /**
     * @throws MailException
     */
    public function sendRecoverIdentityMail(User $user): bool;
}
