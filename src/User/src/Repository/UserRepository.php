<?php

declare(strict_types=1);

namespace Api\User\Repository;

use Api\App\Common\Pagination;
use Api\User\Collection\UserCollection;
use Api\User\Entity\User;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM;
use Doctrine\ORM\EntityRepository;
use Exception;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;

/**
 * Class UserRepository
 * @package Api\User\Repository
 */
class UserRepository extends EntityRepository
{
    /**
     * @param string $email
     * @return bool
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function deleteAccessTokens(string $email)
    {
        if (empty($email)) {
            return false;
        }

        try {
            $stmt = $this->_em->getConnection()->prepare(
                'DELETE FROM `oauth_access_tokens` WHERE `user_id` LIKE :email'
            );
            $stmt->bindValue('email', $email);
            return $stmt->execute();
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param User $user
     * @return null
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
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
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * @param string $email
     * @param string|null $uuid
     * @return int|mixed|string|null
     */
    public function emailExists(string $email = '', ?string $uuid = '')
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
        } catch (Exception $exception) {
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
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * @param array $filters
     * @return UserCollection
     */
    public function getUsers(array $filters = [])
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
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('user.email', ':search'),
                    $qb->expr()->like('detail.firstname', ':search'),
                    $qb->expr()->like('detail.lastname', ':search')
                )
            )->setParameter('search', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['role'])) {
            $qb->andWhere('roles.name = :role')->setParameter('role', $filters['role']);
        }

        // Order results
        $qb->orderBy(($filters['order'] ?? 'user.created'), $filters['dir'] ?? 'desc');

        // Paginate results
        $page = Pagination::getOffsetAndLimit($filters);
        $qb->setFirstResult($page['offset'])->setMaxResults($page['limit']);

        $qb->getQuery()->useQueryCache(true);

        // Return results
        return new UserCollection($qb, false);
    }

    /**
     * @param string $email
     * @return bool
     */
    public function revokeAccessTokens(string $email)
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
            $stmt->execute();
            $tokenIds = $stmt->fetchAll(FetchMode::COLUMN);

            /**
             * ... and mark them as revoked.
             * (Do this because: In case users have a valid refresh token, they could use it to get a new access token.)
             */
            if (!empty($tokenIds)) {
                $stmt = $connection->prepare(
                    'UPDATE `oauth_refresh_tokens` SET `revoked` = 1 WHERE `access_token_id` IN(:tokenIds)'
                );
                $stmt->bindValue('tokenIds', implode("', '", $tokenIds));
                $stmt->execute();
            }

            /**
             * Also, mark access tokens as revoked.
             */
            $stmt = $connection->prepare(
                'UPDATE `oauth_access_tokens` SET `revoked` = 1 WHERE `user_id` LIKE :email'
            );
            $stmt->bindValue('email', $email);
            $stmt->execute();
        } catch (Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * @param User $user
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     */
    public function saveUser(User $user)
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
