<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\NotFoundException;
use Api\App\Message;
use Api\User\Collection\UserRoleCollection;
use Api\User\Entity\UserRole;
use Api\User\Repository\UserRoleRepository;
use Dot\DependencyInjection\Attribute\Inject;

use function in_array;
use function sprintf;

class UserRoleService implements UserRoleServiceInterface
{
    #[Inject(
        UserRoleRepository::class,
    )]
    public function __construct(
        protected UserRoleRepository $roleRepository,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function findOneBy(array $params = []): UserRole
    {
        $role = $this->roleRepository->findOneBy($params);
        if (! $role instanceof UserRole) {
            throw new NotFoundException(Message::ROLE_NOT_FOUND);
        }

        return $role;
    }

    /**
     * @throws BadRequestException
     */
    public function getRoles(array $params = []): UserRoleCollection
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

        return $this->roleRepository->getRoles($params);
    }
}
