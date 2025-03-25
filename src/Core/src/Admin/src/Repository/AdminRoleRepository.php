<?php

declare(strict_types=1);

namespace Core\Admin\Repository;

use Core\Admin\Entity\AdminRole;
use Core\App\Exception\BadRequestException;
use Core\App\Helper\PaginationHelper;
use Core\App\Message;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Dot\DependencyInjection\Attribute\Entity;

use function in_array;
use function sprintf;

/**
 * @extends EntityRepository<object>
 */
#[Entity(name: AdminRole::class)]
class AdminRoleRepository extends EntityRepository
{
    /**
     * @throws BadRequestException
     */
    public function getAdminRoles(array $params = []): QueryBuilder
    {
        $page = PaginationHelper::getOffsetAndLimit($params);

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

        $queryBuilder = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['role'])
            ->from(AdminRole::class, 'role')
            ->orderBy($params['order'], $params['dir'])
            ->setFirstResult($page['offset'])
            ->setMaxResults($page['limit']);
        $queryBuilder->getQuery()->useQueryCache(true);

        return $queryBuilder;
    }
}
