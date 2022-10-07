<?php

declare(strict_types=1);

namespace Api\User\Repository;

use Api\Admin\Entity\Admin;
use Api\App\Helper\PaginationHelper;
use Api\App\Message;
use Api\User\Collection\UserCollection;
use Api\User\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Mezzio\Authentication\OAuth2\Entity\UserEntity;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Dot\AnnotatedServices\Annotation\Entity;
use Throwable;

/**
 * Class UserRepository
 * @package Api\User\Repository
 *
 * @Entity(name="Api\User\Entity\User")
 */
class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    /**
     * @param string $email
     * @return bool
     */
    public function deleteAccessTokens(string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        try {
            $stmt = $this->_em->getConnection()->prepare(
                'DELETE FROM `oauth_access_tokens` WHERE `user_id` LIKE :email'
            );
            $stmt->bindValue('email', $email);
            $stmt->executeStatement();

            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }

    /**
     * @param User $user
     * @return null
     */
    public function deleteUser(User $user)
    {
        $em = $this->getEntityManager();
        $em->remove($user);
        $em->flush();

        return null;
    }

    /**
     * @param string $identity
     * @param string|null $uuid
     * @return int|mixed|string|null
     */
    public function exists(string $identity = '', ?string $uuid = '')
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('user')
            ->from(User::class, 'user')
            ->andWhere('user.identity = :identity')->setParameter('identity', $identity);
        if (!empty($uuid)) {
            $qb->andWhere('user.uuid != :uuid')->setParameter('uuid', $uuid, UuidBinaryOrderedTimeType::NAME);
        }

        try {
            return $qb->getQuery()->useQueryCache(true)->getSingleResult();
        } catch (Throwable $exception) {
            return null;
        }
    }

    /**
     * @param string $email
     * @param string|null $uuid
     * @return User|null
     */
    public function emailExists(string $email = '', ?string $uuid = ''): ?User
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('user')
            ->from(User::class, 'user')
            ->join('user.detail', 'user_detail')
            ->andWhere('user_detail.email = :email')->setParameter('email', $email);
        if (!empty($uuid)) {
            $qb->andWhere('user.uuid != :uuid')->setParameter('uuid', $uuid, UuidBinaryOrderedTimeType::NAME);
        }

        try {
            return $qb->getQuery()->useQueryCache(true)->getSingleResult();
        } catch (Throwable $exception) {
            return null;
        }
    }

    /**
     * @param string $hash
     * @return User|null
     */
    public function findByResetPasswordHash(string $hash): ?User
    {
        try {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select(['user', 'resetPasswords'])->from(User::class, 'user')
                ->leftJoin('user.resetPasswords', 'resetPasswords')
                ->andWhere('resetPasswords.hash = :hash')->setParameter('hash', $hash);

            return $qb->getQuery()->useQueryCache(true)->getSingleResult();
        } catch (Throwable $exception) {
            return null;
        }
    }

    /**
     * @param array $filters
     * @return UserCollection
     */
    public function getUsers(array $filters = []): UserCollection
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select(['user', 'avatar', 'detail', 'roles'])
            ->from(User::class, 'user')
            ->leftJoin('user.avatar', 'avatar')
            ->leftJoin('user.detail', 'detail')
            ->leftJoin('user.roles', 'roles');

        // Filter results
        if (!empty($filters['status'])) {
            $qb->andWhere('user.status = :status')->setParameter('status', $filters['status']);
        }
        if (isset($filters['deleted'])) {
            switch ($filters['deleted']) {
                case 'true':
                    $qb->andWhere('user.isDeleted = :isDeleted')->setParameter('isDeleted', true);
                    break;
                case 'false':
                    $qb->andWhere('user.isDeleted = :isDeleted')->setParameter('isDeleted', false);
                    break;
            }
        }
        if (!empty($filters['search'])) {
            /** @psalm-suppress TooManyArguments */
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('user.identity', ':search'),
                    $qb->expr()->like('detail.firstName', ':search'),
                    $qb->expr()->like('detail.lastName', ':search'),
                    $qb->expr()->like('detail.email', ':search')
                )
            )->setParameter('search', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['role'])) {
            $qb->andWhere('roles.name = :role')->setParameter('role', $filters['role']);
        }

        // Order results
        $qb->orderBy(($filters['order'] ?? 'user.created'), $filters['dir'] ?? 'desc');

        // Paginate results
        $page = PaginationHelper::getOffsetAndLimit($filters);
        $qb->setFirstResult($page['offset'])->setMaxResults($page['limit']);

        $qb->getQuery()->useQueryCache(true);

        // Return results
        return new UserCollection($qb, false);
    }

    /**
     * @param string $email
     * @return bool
     */
    public function revokeAccessTokens(string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        try {
            $connection = $this->_em->getConnection();

            /**
             * Get user's access token ids...
             */
            $stmt = $connection->prepare(
                'SELECT `id` FROM `oauth_access_tokens` WHERE `user_id` LIKE :email AND `revoked` = :revoked'
            );
            $stmt->bindValue('email', $email);
            $stmt->bindValue('revoked', 0);
            $result = $stmt->executeQuery();
            $tokenIds = $result->fetchFirstColumn();

            /**
             * ... and mark them as revoked.
             * (Do this because: In case users have a valid refresh token, they could use it to get a new access token.)
             */
            if (!empty($tokenIds)) {
                $connection->executeQuery(
                    'UPDATE `oauth_refresh_tokens` SET `revoked` = 1 WHERE `access_token_id` IN(?)',
                    [$tokenIds],
                    [Connection::PARAM_STR_ARRAY]
                );

                $stmt->executeStatement();
            }

            /**
             * Also, mark access tokens as revoked.
             */
            $stmt = $connection->prepare(
                'UPDATE `oauth_access_tokens` SET `revoked` = 1 WHERE `user_id` LIKE :email'
            );
            $stmt->bindValue('email', $email);
            $stmt->executeStatement();
        } catch (Throwable $exception) {
            return false;
        }

        return true;
    }

    /**
     * @param User $user
     * @return void
     */
    public function saveUser(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntity {
        $qb = $this->getEntityManager()->createQueryBuilder();
        switch ($clientEntity->getName()) {
            case 'admin':
                $qb->select('a.password')
                    ->from(Admin::class, 'a')
                    ->andWhere('a.identity = :identity')
                    ->setParameter('identity', $username);
                break;
            case 'frontend':
                $qb->select(['u.password', 'u.status'])
                    ->from(User::class, 'u')
                    ->andWhere('u.identity = :identity')
                    ->andWhere('u.isDeleted = 0')
                    ->setParameter('identity', $username);
                break;
            default:
                throw new OAuthServerException(Message::INVALID_CLIENT_ID, 6, 'invalid_client', 401);
        }

        $result = $qb->getQuery()->getArrayResult();
        if (empty($result) || empty($result[0])) {
            return null;
        }

        $result = $result[0];

        if (!password_verify($password, $result['password'])) {
            return null;
        }

        if ($clientEntity->getName() == 'frontend' && $result['status'] !== User::STATUS_ACTIVE) {
            throw new OAuthServerException(Message::USER_NOT_ACTIVATED, 6, 'inactive_user', 401);
        }

        return new UserEntity($username);
    }
}
