<?php

declare(strict_types=1);

namespace Api\User\Repository;

use Api\Admin\Entity\Admin;
use Api\App\Entity\OAuthClient;
use Api\App\Helper\PaginationHelper;
use Api\App\Message;
use Api\User\Collection\UserCollection;
use Api\User\Entity\User;
use Api\User\Enum\UserStatusEnum;
use Doctrine\ORM\EntityRepository;
use Dot\DependencyInjection\Attribute\Entity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Mezzio\Authentication\OAuth2\Entity\UserEntity;

use function password_verify;

/**
 * @extends EntityRepository<object>
 */
#[Entity(name: User::class)]
class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    public function deleteUser(User $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }

    public function getUsers(array $filters = []): UserCollection
    {
        $page = PaginationHelper::getOffsetAndLimit($filters);

        $qb = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['user', 'avatar', 'detail', 'roles'])
            ->from(User::class, 'user')
            ->leftJoin('user.avatar', 'avatar')
            ->leftJoin('user.detail', 'detail')
            ->leftJoin('user.roles', 'roles')
            ->orderBy($filters['order'] ?? 'user.created', $filters['dir'] ?? 'desc')
            ->setFirstResult($page['offset'])
            ->setMaxResults($page['limit']);

        if (! empty($filters['status'])) {
            $qb->andWhere('user.status = :status')->setParameter('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('user.identity', ':search'),
                    $qb->expr()->like('detail.firstName', ':search'),
                    $qb->expr()->like('detail.lastName', ':search'),
                    $qb->expr()->like('detail.email', ':search')
                )
            )->setParameter('search', '%' . $filters['search'] . '%');
        }

        if (! empty($filters['role'])) {
            $qb->andWhere('roles.name = :role')->setParameter('role', $filters['role']);
        }

        //ignore deleted users
        $qb->andWhere('user.status != :status')->setParameter('status', UserStatusEnum::Deleted);
        $qb->getQuery()->useQueryCache(true);

        return new UserCollection($qb, false);
    }

    public function saveUser(User $user): User
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $grantType
     * @throws OAuthServerException
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntity {
        $qb = $this->getEntityManager()->createQueryBuilder();
        switch ($clientEntity->getName()) {
            case OAuthClient::NAME_ADMIN:
                $qb->select('a.password')
                    ->from(Admin::class, 'a')
                    ->andWhere('a.identity = :identity')
                    ->setParameter('identity', $username);
                break;
            case OAuthClient::NAME_FRONTEND:
                $qb->select(['u.password', 'u.status'])
                    ->from(User::class, 'u')
                    ->andWhere('u.identity = :identity')
                    ->andWhere('u.status != :status')
                    ->setParameter('identity', $username)
                    ->setParameter('status', UserStatusEnum::Deleted);
                break;
            default:
                throw new OAuthServerException(Message::INVALID_CLIENT_ID, 6, 'invalid_client', 401);
        }

        $result = $qb->getQuery()->getArrayResult();
        if (empty($result) || empty($result[0])) {
            return null;
        }

        $result = $result[0];

        if (! password_verify($password, $result['password'])) {
            return null;
        }

        if ($clientEntity->getName() === 'frontend' && $result['status'] !== UserStatusEnum::Active) {
            throw new OAuthServerException(Message::USER_NOT_ACTIVATED, 6, 'inactive_user', 401);
        }

        return new UserEntity($username);
    }
}
