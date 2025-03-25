<?php

declare(strict_types=1);

namespace Core\Admin\Repository;

use Core\Admin\Entity\Admin;
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
#[Entity(name: Admin::class)]
class AdminRepository extends EntityRepository
{
    public function deleteAdmin(Admin $admin): void
    {
        $this->getEntityManager()->remove($admin);
        $this->getEntityManager()->flush();
    }

    /**
     * @throws BadRequestException
     */
    public function getAdmins(array $params = []): QueryBuilder
    {
        $page = PaginationHelper::getOffsetAndLimit($params);

        $values = [
            'admin.identity',
            'admin.firstName',
            'admin.lastName',
            'admin.status',
            'admin.created',
            'admin.updated',
        ];

        $params['order'] = $params['order'] ?? 'admin.created';
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
            ->select(['admin'])
            ->from(Admin::class, 'admin')
            ->orderBy($params['order'], $params['dir'])
            ->setFirstResult($page['offset'])
            ->setMaxResults($page['limit']);
        $queryBuilder->getQuery()->useQueryCache(true);

        return $queryBuilder;
    }

    public function saveAdmin(Admin $admin): Admin
    {
        $this->getEntityManager()->persist($admin);
        $this->getEntityManager()->flush();

        return $admin;
    }
}
