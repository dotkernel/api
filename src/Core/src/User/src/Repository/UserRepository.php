<?php

declare(strict_types=1);

namespace Core\User\Repository;

use Core\Admin\Entity\Admin;
use Core\App\Message;
use Core\App\Repository\AbstractRepository;
use Core\Security\Entity\OAuthClient;
use Core\User\Entity\User;
use Core\User\Enum\UserStatusEnum;
use Doctrine\ORM\QueryBuilder;
use Dot\DependencyInjection\Attribute\Entity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Mezzio\Authentication\OAuth2\Entity\UserEntity;

use function array_key_exists;
use function is_string;
use function password_verify;
use function strlen;

#[Entity(name: User::class)]
class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    /**
     * @param array<non-empty-string, mixed> $params
     * @param array<non-empty-string, mixed> $filters
     */
    public function getUsers(array $params = [], array $filters = []): QueryBuilder
    {
        $queryBuilder = $this
            ->getQueryBuilder()
            ->select(['user'])
            ->from(User::class, 'user')
            ->leftJoin('user.detail', 'detail')
            ->leftJoin('user.roles', 'role')
            ->andWhere('user.status != :statusNotDeleted')
            ->setParameter('statusNotDeleted', UserStatusEnum::Deleted);

        if (
            array_key_exists('identity', $filters)
            && is_string($filters['identity'])
            && strlen($filters['identity']) > 0
        ) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->like('user.identity', ':identity'))
                ->setParameter('identity', '%' . $filters['identity'] . '%');
        }
        if (
            array_key_exists('email', $filters)
            && is_string($filters['email'])
            && strlen($filters['email']) > 0
        ) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->like('detail.email', ':email'))
                ->setParameter('email', '%' . $filters['email'] . '%');
        }
        if (
            array_key_exists('status', $filters)
            && is_string($filters['status'])
            && strlen($filters['status']) > 0
        ) {
            $queryBuilder
                ->andWhere('user.status = :status')
                ->setParameter('status', $filters['status']);
        }
        if (
            array_key_exists('role', $filters)
            && is_string($filters['role'])
            && strlen($filters['role']) > 0
        ) {
            $queryBuilder
                ->andWhere('role.name = :role')
                ->setParameter('role', $filters['role']);
        }

        $queryBuilder
            ->orderBy($params['sort'], $params['dir'])
            ->setFirstResult($params['offset'])
            ->setMaxResults($params['limit'])
            ->groupBy('user.uuid');
        $queryBuilder->getQuery()->useQueryCache(true);

        return $queryBuilder;
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
