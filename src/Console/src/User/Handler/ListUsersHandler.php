<?php

declare(strict_types=1);

namespace Api\Console\User\Handler;

use Api\App\Message;
use Api\User\Entity\User;
use Api\User\Entity\UserRole;
use Api\User\Service\UserService;
use Dot\AnnotatedServices\Annotation\Inject;
use Dot\AnnotatedServices\Annotation\Service;
use Dot\Console\Command\AbstractCommand;
use Exception;
use Laminas\Console\Adapter\AdapterInterface;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Text\Table\Table;
use Laminas\Validator\Digits;
use Laminas\Validator\InArray;
use ZF\Console\Route;

use function array_map;
use function implode;
use function min;
use function sprintf;

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
     * @throws Exception
     */
    public function __invoke(Route $route, AdapterInterface $console)
    {
        $params = $this->validateParams($route->getMatches());

        $users = $this->userService->getUsers($params);
        if ($users->count() == 0) {
            $console->write('Nothing to display.');
            exit;
        }

        $table = new Table(['columnWidths' => [38, 30, 30, 10, 20, 21, 21]]);
        $table->setAutoSeparate(Table::AUTO_SEPARATE_HEADER);
        $table->setPadding(1);
        $table->appendRow(['UUID', 'Name', 'Email', 'Status', 'Role(s)', 'Created', 'Updated']);

        /** @var User $user */
        foreach ($users as $user) {
            $table->appendRow([
                $user->getUuid()->toString(),
                $user->getName(),
                $user->getIdentity(),
                $user->getStatus(),
                implode(', ', array_map(function (UserRole $role) {
                    return $role->getName();
                }, $user->getRoles()->getIterator()->getArrayCopy())),
                $user->getCreatedFormatted(),
                $user->getUpdatedFormatted()
            ]);
        }

        $console->writeLine(
            sprintf(
                'Showing %d-%d of %d user(s).',
                $users->getQuery()->getFirstResult() + 1,
                min($users->count(), $params['page'] * $users->getQuery()->getMaxResults()),
                $users->count()
            )
        );
        $console->write($table->render());
    }

    /**
     * @param array $params
     * @return array
     * @throws Exception
     */
    private function validateParams(array $params): array
    {
        $validator = new Digits();
        if (!$validator->isValid($params['page'])) {
            throw new Exception(sprintf(Message::INVALID_VALUE, 'page'));
        }

        if (!empty($params['search'])) {
            $params['search'] = (new StringTrim())->filter($params['search']);
            $params['search'] = (new StripTags())->filter($params['search']);
        }

        if (!empty($params['status'])) {
            $validator = new InArray();
            $validator->setHaystack(User::STATUSES);
            if (!$validator->isValid($params['status'])) {
                throw new Exception(sprintf(Message::INVALID_VALUE, 'status'));
            }
        }

        if (!empty($params['deleted'])) {
            $validator = new InArray();
            $validator->setHaystack(['true', 'false']);
            if (!$validator->isValid($params['deleted'])) {
                throw new Exception(sprintf(Message::INVALID_VALUE, 'deleted'));
            }
        }

        return $params;
    }
}
