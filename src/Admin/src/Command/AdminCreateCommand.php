<?php

declare(strict_types=1);

namespace Api\Admin\Command;

use Api\Admin\InputFilter\CreateAdminInputFilter;
use Api\Admin\Service\AdminRoleServiceInterface;
use Api\Admin\Service\AdminServiceInterface;
use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Core\Admin\Entity\AdminRole;
use Core\Admin\Enum\AdminRoleEnum;
use Core\Admin\Enum\AdminStatusEnum;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function implode;
use function sprintf;
use function trim;

use const PHP_EOL;

#[AsCommand(
    name: 'admin:create',
    description: 'Create admin account.',
)]
class AdminCreateCommand extends Command
{
    /** @var string $defaultName */
    protected static $defaultName = 'admin:create';

    #[Inject(
        AdminServiceInterface::class,
        AdminRoleServiceInterface::class,
    )]
    public function __construct(
        protected AdminServiceInterface $adminService,
        protected AdminRoleServiceInterface $adminRoleService,
    ) {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Create admin account.')
            ->addUsage('-i myIdentity -p myPassword -f myFirstName -l myLastName')
            ->addUsage('--identity myIdentity --password myPassword --firstName myFirstName --lastName myLastName')
            ->addUsage('--identity=myIdentity --password=myPassword --firstName=myFirstName --lastName=myLastName')
            ->addOption('identity', 'i', InputOption::VALUE_REQUIRED, 'Admin identity')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Admin password')
            ->addOption('firstName', 'f', InputOption::VALUE_REQUIRED, 'Admin first name')
            ->addOption('lastName', 'l', InputOption::VALUE_REQUIRED, 'Admin last name');
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws Exception
     * @throws NotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputFilter = (new CreateAdminInputFilter())->setData($this->getData($input));
        if (! $inputFilter->isValid()) {
            $messages = [];
            foreach ($inputFilter->getMessages() as $field => $errors) {
                foreach ($errors as $error) {
                    /** @var scalar $error */
                    $messages[] = sprintf('%s: %s', $field, $error);
                }
            }

            throw new Exception(implode(PHP_EOL, $messages));
        }

        $this->adminService->saveAdmin($inputFilter->getValues());

        (new SymfonyStyle($input, $output))->info(Message::ADMIN_CREATED);

        return Command::SUCCESS;
    }

    /**
     * @return array{
     *     identity: string,
     *     password: string,
     *     passwordConfirm: string,
     *     firstName: string,
     *     lastName: string,
     *     status: 'active',
     *     roles: array{uuid: non-empty-string}[],
     * }
     * @throws Exception
     */
    private function getData(InputInterface $input): array
    {
        $adminRole = $this->adminRoleService->getAdminRoleRepository()->findOneBy(['name' => AdminRoleEnum::Admin]);
        if (! $adminRole instanceof AdminRole) {
            throw new Exception(Message::ROLE_NOT_FOUND);
        }

        return [
            'identity'        => trim($input->getOption('identity')),
            'password'        => trim($input->getOption('password')),
            'passwordConfirm' => trim($input->getOption('password')),
            'firstName'       => trim($input->getOption('firstName')),
            'lastName'        => trim($input->getOption('lastName')),
            'status'          => AdminStatusEnum::Active->value,
            'roles'           => [
                ['uuid' => $adminRole->getUuid()->toString()],
            ],
        ];
    }
}
