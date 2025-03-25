<?php

declare(strict_types=1);

namespace Core\User\Repository;

use Core\Admin\Entity\Admin;
use Core\App\Exception\BadRequestException;
use Core\App\Helper\PaginationHelper;
use Core\App\Message;
use Core\Security\Entity\OAuthClient;
use Core\User\Entity\User;
use Core\User\Enum\UserStatusEnum;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Dot\DependencyInjection\Attribute\Entity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Mezzio\Authentication\OAuth2\Entity\UserEntity;

use function in_array;
use function password_verify;
use function sprintf;

/**
 * @extends EntityRepository<object>
 */
#[Entity(name: User::class)]
class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    /**
     * @throws BadRequestException
     */
    public function getUsers(array $params = []): QueryBuilder
    {
        $page = PaginationHelper::getOffsetAndLimit($params);

        $values = [
            'user.identity',
            'user.status',
            'user.created',
            'user.updated',
        ];

        $params['order'] = $params['order'] ?? 'user.created';
        if (! in_array($params['order'], $values)) {
            throw (new BadRequestException())->setMessages([sprintf(Message::INVALID_VALUE, 'order')]);
        }
        $params['dir'] = $params['dir'] ?? 'desc';
        if (! in_array($params['dir'], ['asc', 'desc'])) {
            throw (new BadRequestException())->setMessages([sprintf(Message::INVALID_VALUE, 'dir')]);
        }

        $qb = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['user', 'avatar', 'detail', 'roles'])
            ->from(User::class, 'user')
            ->leftJoin('user.avatar', 'avatar')
            ->leftJoin('user.detail', 'detail')
            ->leftJoin('user.roles', 'roles')
            ->andWhere('user.status != :status')
            ->setParameter('status', UserStatusEnum::Deleted)
            ->orderBy($params['order'], $params['dir'])
            ->setFirstResult($page['offset'])
            ->setMaxResults($page['limit']);
        $qb->getQuery()->useQueryCache(true);

        if (! empty($params['status'])) {
            $qb->andWhere('user.status = :status')->setParameter('status', $params['status']);
        }

        if (! empty($params['search'])) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('user.identity', ':search'),
                    $qb->expr()->like('detail.firstName', ':search'),
                    $qb->expr()->like('detail.lastName', ':search'),
                    $qb->expr()->like('detail.email', ':search')
                )
            )->setParameter('search', '%' . $params['search'] . '%');
        }

        if (! empty($params['role'])) {
            $qb->andWhere('roles.name = :role')->setParameter('role', $params['role']);
        }

        return $qb;
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
