<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\App\Exception\NotFoundException;
use Core\App\Message;
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
}
