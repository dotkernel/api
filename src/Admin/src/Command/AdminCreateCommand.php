<?php

declare(strict_types=1);

namespace Api\Admin\Command;

use Api\Admin\InputFilter\CreateAdminInputFilter;
use Core\Admin\Entity\AdminRole;
use Core\Admin\Service\AdminRoleServiceInterface;
use Core\Admin\Service\AdminServiceInterface;
use Core\App\Exception\BadRequestException;
use Core\App\Exception\ConflictException;
use Core\App\Exception\NotFoundException;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function implode;
use function sprintf;

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
     * @throws NotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputFilter = (new CreateAdminInputFilter())->setData($this->getData($input));
        if (! $inputFilter->isValid()) {
            $messages = [];
            foreach ($inputFilter->getMessages() as $field => $errors) {
                foreach ($errors as $error) {
                    $messages[] = sprintf('%s: %s', $field, $error);
                }
            }

            throw new BadRequestException(implode(PHP_EOL, $messages));
        }

        $this->adminService->createAdmin($inputFilter->getValues());

        (new SymfonyStyle($input, $output))->info(Message::ADMIN_CREATED);

        return Command::SUCCESS;
    }

    /**
     * @throws NotFoundException
     */
    private function getData(InputInterface $input): array
    {
        $adminRole = $this->adminRoleService->getAdminRoleRepository()->findOneBy(['name' => AdminRole::ROLE_ADMIN]);
        if (! $adminRole instanceof AdminRole) {
            throw new NotFoundException(Message::ROLE_NOT_FOUND);
        }

        return [
            'identity'        => $input->getOption('identity'),
            'password'        => $input->getOption('password'),
            'passwordConfirm' => $input->getOption('password'),
            'firstName'       => $input->getOption('firstName'),
            'lastName'        => $input->getOption('lastName'),
            'roles'           => [
                ['uuid' => $adminRole->getUuid()->toString()],
            ],
        ];
    }
}
