<?php

declare(strict_types=1);

namespace App\User\Repository;

use Exception;
use App\User\Collection\UserCollection;
use App\User\Entity\UserEntity;
use App\User\Entity\UserRoleEntity;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;

/**
 * Class UserRepository
 * @package App\User\Repository
 */
class UserRepository extends EntityRepository
{
    /**
     * @param UserEntity $user
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteUser(UserEntity $user)
    {
        $em = $this->getEntityManager();
        $em->remove($user);
        $em->flush();
    }

    /**
     * @param string $name
     * @return UserRoleEntity
     */
    public function getRole(string $name = '')
    {
        $repository = $this->getEntityManager()->getRepository(UserRoleEntity::class);

        /** @var UserRoleEntity $role */
        $role = $repository->findOneBy([
            'name' => $name
        ]);

        return $role;
    }

    /**
     * @param string $email
     * @param null|string $uuid
     * @return UserEntity|null
     */
    public function getUser(string $email = '', ?string $uuid = '')
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('user')
            ->from(UserEntity::class, 'user')
            ->andWhere('user.email = :email')->setParameter('email', $email);

        // Email exists and it does not belong to the user identified by $uuid
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
        if (!empty($filters['status'])) {
            $qb->andWhere('user.status = :status')->setParameter('status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('user.email', ':search'),
                    $qb->expr()->like('detail.firstname', ':search'),
                    $qb->expr()->like('detail.lastname', ':search')
                )
            )
            ->setParameter('search', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['role'])) {
            $qb->andWhere('roles.name = :role')->setParameter('role', $filters['role']);
        }

        $qb->setFirstResult((int)($filters['offset'] ?? 0))->setMaxResults((int)($filters['limit'] ?? 5));

        return new UserCollection($qb, false);
    }

    /**
     * @param UserEntity $user
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveUser(UserEntity $user)
    {
        $user->touch();

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
