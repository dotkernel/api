<?php

declare(strict_types=1);

namespace Api\Admin\Service;

use Api\Admin\Collection\AdminRoleCollection;
use Api\App\Exception\BadRequestException;
use Api\App\Exception\NotFoundException;
use Core\Admin\Entity\AdminRole;
use Core\Admin\Repository\AdminRoleRepository;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;

use function in_array;
use function sprintf;

class AdminRoleService implements AdminRoleServiceInterface
{
    #[Inject(
        AdminRoleRepository::class,
    )]
    public function __construct(
        protected AdminRoleRepository $adminRoleRepository,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function findOneBy(array $params = []): AdminRole
    {
        $role = $this->adminRoleRepository->findOneBy($params);
        if (! $role instanceof AdminRole) {
            throw new NotFoundException(Message::ROLE_NOT_FOUND);
        }

        return $role;
    }

    /**
     * @throws BadRequestException
     */
    public function getAdminRoles(array $params = []): AdminRoleCollection
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

        $qb = $this->adminRoleRepository->getAdminRoles($params);
        $qb->getQuery()->useQueryCache(true);

        return new AdminRoleCollection($qb, false);
    }
}
