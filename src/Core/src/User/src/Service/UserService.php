<?php

declare(strict_types=1);

namespace Core\User\Service;

use Core\App\Exception\BadRequestException;
use Core\App\Exception\ConflictException;
use Core\App\Exception\NotFoundException;
use Core\App\Message;
use Core\Security\Repository\OAuthAccessTokenRepository;
use Core\Security\Repository\OAuthRefreshTokenRepository;
use Core\User\Entity\User;
use Core\User\Entity\UserDetail;
use Core\User\Entity\UserRole;
use Core\User\Enum\UserStatusEnum;
use Core\User\Repository\UserDetailRepository;
use Core\User\Repository\UserRepository;
use Core\User\Repository\UserRoleRepository;
use Dot\DependencyInjection\Attribute\Inject;
use Ramsey\Uuid\UuidInterface;

use function count;
use function date;
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
        return $this->userRepository->saveUser($user->activate());
    }

    public function deactivateUser(User $user): User
    {
        return $this->userRepository->saveUser($user->deactivate());
    }

    /**
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function createUser(array $data = []): User
    {
        $detail = (new UserDetail())
            ->setFirstName($data['detail']['firstName'] ?? null)
            ->setLastName($data['detail']['lastName'] ?? null)
            ->setEmail($data['detail']['email']);

        $user = (new User())
            ->setDetail($detail)
            ->setIdentity($data['identity'])
            ->usePassword($data['password'])
            ->setStatus($data['status'] ?? UserStatusEnum::Pending);
        $detail->setUser($user);

        $this->validateUniqueUser($user->getIdentity(), $user->getDetail()->getEmail());

        if (isset($data['roles']) && is_array($data['roles']) && count($data['roles']) > 0) {
            foreach ($data['roles'] as $roleData) {
                $userRole = $this->userRoleRepository->find($roleData['uuid']);
                if (! $userRole instanceof UserRole) {
                    throw new NotFoundException(Message::ROLE_NOT_FOUND);
                }
                $user->addRole($userRole);
            }
        }

        return $this->userRepository->saveUser($user);
    }

    public function deleteUser(User $user): User
    {
        $this->revokeTokens($user);

        return $this->anonymizeUser($user);
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

        return $this->userRepository->saveUser($user);
    }

    private function getAnonymousPlaceholder(): string
    {
        return 'anonymous' . date('dmYHis');
    }

    /**
     * @throws ConflictException
     */
    private function validateUniqueUser(string $identity, string $email, ?UuidInterface $uuid = null): void
    {
        $user = $this->userRepository->findOneBy(['identity' => $identity]);
        if ($user instanceof User) {
            if ($uuid === null) {
                throw new ConflictException(Message::DUPLICATE_IDENTITY);
            }
            if ($user->getUuid()->toString() !== $uuid->toString()) {
                throw new ConflictException(Message::DUPLICATE_IDENTITY);
            }
        }

        $userDetail = $this->userDetailRepository->findOneBy(['email' => $email]);
        if ($userDetail instanceof UserDetail) {
            if ($uuid === null) {
                throw new ConflictException(Message::DUPLICATE_EMAIL);
            }
            if ($userDetail->getUser()->getUuid()->toString() !== $uuid->toString()) {
                throw new ConflictException(Message::DUPLICATE_EMAIL);
            }
        }
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
     * @throws NotFoundException
     */
    public function find(string $id): User
    {
        $user = $this->userRepository->find($id);
        if (! $user instanceof User || $user->isDeleted()) {
            throw new NotFoundException(Message::USER_NOT_FOUND);
        }

        return $user;
    }

    /**
     * @throws NotFoundException
     */
    public function findByEmail(string $email): User
    {
        $userDetail = $this->userDetailRepository->findOneBy(['email' => $email]);
        if (! $userDetail instanceof UserDetail) {
            throw new NotFoundException(Message::USER_NOT_FOUND);
        }

        $user = $userDetail->getUser();
        if ($user->isDeleted()) {
            throw new NotFoundException(Message::USER_NOT_FOUND);
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
            throw new NotFoundException(Message::USER_NOT_FOUND);
        }

        return $user;
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function updateUser(User $user, array $data = []): User
    {
        if (isset($data['identity'])) {
            $user->setIdentity($data['identity']);
        }

        if (isset($data['password'])) {
            $user->usePassword($data['password']);
        }

        if (isset($data['status'])) {
            $user->setStatus($data['status']);
        }

        if (isset($data['hash'])) {
            $user->setHash($data['hash']);
        }

        if (isset($data['detail']['firstName'])) {
            $user->getDetail()->setFirstname($data['detail']['firstName']);
        }

        if (isset($data['detail']['lastName'])) {
            $user->getDetail()->setLastName($data['detail']['lastName']);
        }

        if (isset($data['detail']['email'])) {
            $user->getDetail()->setEmail($data['detail']['email']);
        }

        $this->validateUniqueUser($user->getIdentity(), $user->getDetail()->getEmail(), $user->getUuid());

        if (isset($data['roles']) && is_array($data['roles']) && count($data['roles']) > 0) {
            $user->resetRoles();
            foreach ($data['roles'] as $roleData) {
                $userRole = $this->userRoleRepository->find($roleData['uuid']);
                if (! $userRole instanceof UserRole) {
                    throw new NotFoundException(Message::ROLE_NOT_FOUND);
                }
                $user->addRole($userRole);
            }
        }

        if (! $user->hasRoles()) {
            throw (new BadRequestException())->setMessages([Message::RESTRICTION_ROLES]);
        }

        return $this->userRepository->saveUser($user);
    }
}
