<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\App\Exception\NotFoundException;
use Core\App\Message;
use Core\User\Entity\User;
use Core\User\Entity\UserResetPassword;
use Core\User\Repository\UserResetPasswordRepository;
use Dot\DependencyInjection\Attribute\Inject;

class UserResetPasswordService implements UserResetPasswordServiceInterface
{
    #[Inject(
        UserResetPasswordRepository::class,
    )]
    public function __construct(
        protected UserResetPasswordRepository $userResetPasswordRepository,
    ) {
    }

    public function getUserResetPasswordRepository(): UserResetPasswordRepository
    {
        return $this->userResetPasswordRepository;
    }

    /**
     * @param array<string, mixed> $params
     * @throws NotFoundException
     */
    public function findOneBy(array $params): UserResetPassword
    {
        $userResetPassword = $this->userResetPasswordRepository->findOneBy($params);
        if (! $userResetPassword instanceof UserResetPassword) {
            throw NotFoundException::create(Message::RESET_PASSWORD_NOT_FOUND);
        }

        return $userResetPassword;
    }

    public function saveResetPassword(User $user): UserResetPassword
    {
        $resetPassword = (new UserResetPassword())->setUser($user);
        $user->addResetPassword($resetPassword);

        $this->userResetPasswordRepository->saveResource($resetPassword);

        return $resetPassword;
    }
}
