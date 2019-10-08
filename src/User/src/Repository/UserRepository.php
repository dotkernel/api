<?php

declare(strict_types=1);

namespace Api\User\Repository;

use Api\App\Common\Pagination;
use Api\User\Collection\UserCollection;
use Api\User\Entity\UserEntity;
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
     * @param UserEntity $user
     * @return null
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     */
    public function deleteUser(UserEntity $user)
    {
        $em = $this->getEntityManager();
        $em->remove($user);
        $em->flush();

        return null;
    }

    /**
     * @param string $email
     * @param string|null $uuid
     * @return UserEntity|null
     */
    public function exists(string $email = '', ?string $uuid = '')
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('user')
            ->from(UserEntity::class, 'user')
            ->andWhere('user.email = :email')->setParameter('email', $email)
            ->andWhere('user.isDeleted = 0');
        if (!empty($uuid)) {
            $qb->andWhere('user.uuid != :uuid')->setParameter('uuid', $uuid, UuidBinaryOrderedTimeType::NAME);
        }

        try {
            return $qb->getQuery()->getSingleResult();
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
            ->from(UserEntity::class, 'user')
            ->leftJoin('user.avatar', 'avatar')
            ->leftJoin('user.detail', 'detail')
            ->leftJoin('user.roles', 'roles');

        // Filter results
        if (!empty($filters['status'])) {
            $qb->andWhere('user.status = :status')->setParameter('status', $filters['status']);
        }
        if (isset($filters['deleted'])) {
            switch ($filters['deleted']) {
                case 'yes':
                    $qb->andWhere('user.isDeleted = :isDeleted')->setParameter('isDeleted', true);
                    break;
                case 'no':
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
     * @param UserEntity $user
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     */
    public function saveUser(UserEntity $user)
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
