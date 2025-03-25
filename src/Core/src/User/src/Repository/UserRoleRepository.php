<?php

declare(strict_types=1);

namespace Core\User\Repository;

use Core\App\Exception\BadRequestException;
use Core\App\Helper\PaginationHelper;
use Core\App\Message;
use Core\User\Entity\UserRole;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Dot\DependencyInjection\Attribute\Entity;

use function in_array;
use function sprintf;

/**
 * @extends EntityRepository<object>
 */
#[Entity(name: UserRole::class)]
class UserRoleRepository extends EntityRepository
{
    /**
     * @throws BadRequestException
     */
    public function getRoles(array $params = []): Query
    {
        $values = [
            'role.name',
            'role.created',
            'role.updated',
        ];

        $params['order'] = $params['order'] ?? 'role.created';
        if (! in_array($params['order'], $values)) {
            throw (new BadRequestException())->setMessages([sprintf(Message::INVALID_VALUE, 'order')]);
        }

        $params['dir'] = $params['dir'] ?? 'desc';
        if (! in_array($params['dir'], ['asc', 'desc'])) {
            throw (new BadRequestException())->setMessages([sprintf(Message::INVALID_VALUE, 'dir')]);
        }

        $page = PaginationHelper::getOffsetAndLimit($params);

        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['role'])
            ->from(UserRole::class, 'role')
            ->orderBy($params['order'], $params['dir'])
            ->setFirstResult($page['offset'])
            ->setMaxResults($page['limit'])
            ->getQuery()
            ->useQueryCache(true);
    }
}
