<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Core\App\Helper\Paginator;
use Core\App\Message;
use Core\Security\Repository\OAuthAccessTokenRepository;
use Core\Security\Repository\OAuthRefreshTokenRepository;
use Core\User\Entity\User;
use Core\User\Entity\UserDetail;
use Core\User\Entity\UserRole;
use Core\User\Enum\UserRoleEnum;
use Core\User\Enum\UserStatusEnum;
use Core\User\Repository\UserDetailRepository;
use Core\User\Repository\UserRepository;
use Core\User\Repository\UserRoleRepository;
use Doctrine\ORM\QueryBuilder;
use Dot\DependencyInjection\Attribute\Inject;
use Ramsey\Uuid\UuidInterface;

use function array_key_exists;
use function count;
use function date;
use function in_array;
use function is_array;

class UserService implements UserServiceInterface
{
    #[Inject(
        OAuthAccessTokenRepository::class,
        OAuthRefreshTokenRepository::class,
        UserRepository::class,
        UserDetailRepository::class,
        UserRoleRepository::class,
        'config',
    )]
    public function __construct(
        protected OAuthAccessTokenRepository $oAuthAccessTokenRepository,
        protected OAuthRefreshTokenRepository $oAuthRefreshTokenRepository,
        protected UserRepository $userRepository,
        protected UserDetailRepository $userDetailRepository,
        protected UserRoleRepository $userRoleRepository,
        protected array $config = [],
    ) {
    }

    public function getUserRepository(): UserRepository
    {
        return $this->userRepository;
    }

    public function activateUser(User $user): User
    {
        $this->userRepository->saveResource($user->activate());

        return $user;
    }

    public function deactivateUser(User $user): User
    {
        $this->userRepository->saveResource($user->deactivate());

        return $user;
    }

    public function deleteUser(User $user): User
    {
        $this->revokeTokens($user);

        return $this->anonymizeUser($user);
    }

    /**
     * @throws NotFoundException
     */
    public function findByEmail(string $email): User
    {
        $userDetail = $this->userDetailRepository->findOneBy(['email' => $email]);
        if (! $userDetail instanceof UserDetail) {
            throw NotFoundException::create(Message::USER_NOT_FOUND);
        }

        $user = $userDetail->getUser();
        if ($user->isDeleted()) {
            throw NotFoundException::create(Message::USER_NOT_FOUND);
        }

        return $user;
    }

    /**
     * @throws NotFoundException
     */
    public function findByIdentity(string $identity): ?User
    {
        return $this->findOneBy(['identity' => $identity]);
    }

    /**
     * @throws NotFoundException
     */
    public function findOneBy(array $params): User
    {
        $user = $this->userRepository->findOneBy($params);
        if (! $user instanceof User || $user->isDeleted()) {
            throw NotFoundException::create(Message::USER_NOT_FOUND);
        }

        return $user;
    }

    /**
     * @throws NotFoundException
     */
    public function findUser(string $id): User
    {
        $user = $this->userRepository->find($id);
        if (! $user instanceof User || $user->isDeleted()) {
            throw NotFoundException::create(Message::USER_NOT_FOUND);
        }

        return $user;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function getUsers(array $params): QueryBuilder
    {
        $filters = $params['filters'] ?? [];
        $params  = Paginator::getParams($params, 'user.created');

        $sortableColumns = [
            'user.identity',
            'user.status',
            'user.created',
            'user.updated',
            'detail.firstName',
            'detail.lastName',
            'detail.email',
            'role.name',
        ];
        if (! in_array($params['sort'], $sortableColumns, true)) {
            $params['sort'] = 'user.created';
        }

        return $this->userRepository->getUsers($params, $filters);
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function saveUser(array $data, ?User $user = null): User
    {
        if (! $user instanceof User) {
            $user = new User();
        }

        if (array_key_exists('identity', $data) && $data['identity'] !== null && ! $user->hasIdentity()) {
            $user->setIdentity($data['identity']);
        }
        if (array_key_exists('password', $data) && $data['password'] !== null) {
            $user->usePassword($data['password']);
        }
        if (array_key_exists('hash', $data) && $data['hash'] !== null) {
            $user->setHash($data['hash']);
        }
        if (array_key_exists('status', $data) && $data['status'] !== null) {
            $status = $data['status'];
            if (! $status instanceof UserStatusEnum) {
                $status = UserStatusEnum::tryFrom($status);
            }
            if (! $status instanceof UserStatusEnum) {
                throw BadRequestException::create(
                    detail: Message::invalidValue('status'),
                    additional: ['errors' => ['status' => $data['status']]]
                );
            }
            $user->setStatus($status);
        }
        if (array_key_exists('detail', $data) && is_array($data['detail'])) {
            if (! $user->hasDetail()) {
                $user->setDetail((new UserDetail())->setUser($user));
            }
            if (array_key_exists('firstName', $data['detail']) && $data['detail']['firstName'] !== null) {
                $user->getDetail()->setFirstname($data['detail']['firstName']);
            }
            if (array_key_exists('lastName', $data['detail']) && $data['detail']['lastName'] !== null) {
                $user->getDetail()->setLastName($data['detail']['lastName']);
            }
            if (array_key_exists('email', $data['detail']) && $data['detail']['email'] !== null) {
                $user->getDetail()->setEmail($data['detail']['email']);
            }
        }

        $this->validateUniqueUser($user->getIdentity(), $user->getDetail()->getEmail(), $user->getUuid());

        if (array_key_exists('roles', $data) && count($data['roles']) > 0) {
            $user->resetRoles();
            foreach ($data['roles'] as $roleData) {
                $userRole = $this->userRoleRepository->find($roleData['uuid']);
                if (! $userRole instanceof UserRole) {
                    throw NotFoundException::create(Message::ROLE_NOT_FOUND);
                }
                $user->addRole($userRole);
            }
        }

        if (! $user->hasRoles()) {
            $user->addRole(
                $this->userRoleRepository->findOneBy(['name' => UserRoleEnum::User])
            );
        }

        $this->userRepository->saveResource($user);

        return $user;
    }

    private function anonymizeUser(User $user): User
    {
        $placeholder = $this->getAnonymousPlaceholder();

        $user
            ->setStatus(UserStatusEnum::Deleted)
            ->setIdentity($placeholder . $this->config['userAnonymizeAppend'])
            ->getDetail()
            ->setFirstName($placeholder)
            ->setLastName($placeholder)
            ->setEmail($placeholder);

        $this->userRepository->saveResource($user);

        return $user;
    }

    private function getAnonymousPlaceholder(): string
    {
        return 'anonymous' . date('dmYHis');
    }

    private function revokeTokens(User $user): void
    {
        $accessTokens = $this->oAuthAccessTokenRepository->findAccessTokens($user->getIdentity());
        foreach ($accessTokens as $accessToken) {
            $this->oAuthAccessTokenRepository->revokeAccessToken($accessToken->getToken());
            $this->oAuthRefreshTokenRepository->revokeRefreshToken($accessToken->getToken());
        }
    }

    /**
     * @throws ConflictException
     */
    private function validateUniqueUser(string $identity, string $email, ?UuidInterface $uuid = null): void
    {
        $user = $this->userRepository->findOneBy(['identity' => $identity]);
        if ($user instanceof User) {
            if ($uuid === null) {
                throw ConflictException::create(Message::DUPLICATE_IDENTITY);
            }
            if ($user->getUuid()->toString() !== $uuid->toString()) {
                throw ConflictException::create(Message::DUPLICATE_IDENTITY);
            }
        }

        $userDetail = $this->userDetailRepository->findOneBy(['email' => $email]);
        if ($userDetail instanceof UserDetail) {
            if ($uuid === null) {
                throw ConflictException::create(Message::DUPLICATE_EMAIL);
            }
            if ($userDetail->getUser()->getUuid()->toString() !== $uuid->toString()) {
                throw ConflictException::create(Message::DUPLICATE_EMAIL);
            }
        }
    }
}
