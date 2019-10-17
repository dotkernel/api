<?php

declare(strict_types=1);

namespace Api\Console\User\Handler;

use Api\User\Entity\UserEntity;
use Api\User\Entity\UserRoleEntity;
use Api\User\Service\UserService;
use Dot\AnnotatedServices\Annotation\Inject;
use Dot\AnnotatedServices\Annotation\Service;
use Dot\Console\Command\AbstractCommand;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Text\Table\Table;
use ZF\Console\Route;

use function array_map;
use function implode;
use function min;

/**
 * Class ListUsersCommand
 *
 * @Service
 */
class ListUsersHandler extends AbstractCommand
{
    /** @var UserService $userService */
    protected $userService;

    /**
     * ListUsersCommand constructor.
     * @param UserService $userService
     *
     * @Inject({UserService::class})
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param Route $route
     * @param AdapterInterface $console
     */
    public function __invoke(Route $route, AdapterInterface $console)
    {
        $params = $route->getMatches();

        $users = $this->userService->getUsers($params);
        if ($users->count() == 0) {
            $console->write('Nothing to display.');
            exit;
        }

        $table = new Table(['columnWidths' => [38, 30, 30, 10, 20, 21, 21]]);
        $table->setAutoSeparate(Table::AUTO_SEPARATE_HEADER);
        $table->setPadding(1);
        $table->appendRow(['UUID', 'Name', 'Email', 'Status', 'Role(s)', 'Created', 'Updated']);

        /** @var UserEntity $user */
        foreach ($users as $user) {
            $table->appendRow([
                $user->getUuid()->toString(),
                $user->getName(),
                $user->getEmail(),
                $user->getStatus(),
                implode(', ', array_map(function (UserRoleEntity $role) {
                    return $role->getName();
                }, $user->getRoles()->getIterator()->getArrayCopy())),
                $user->getCreatedFormatted(),
                $user->getUpdatedFormatted()
            ]);
        }

        $console->writeLine(sprintf('Showing %d-%d of %d user(s).',
            $users->getQuery()->getFirstResult() + 1,
            min($users->count(), $params['page'] * $users->getQuery()->getMaxResults()),
            $users->count()
        ));
        $console->write($table->render());
    }
}
