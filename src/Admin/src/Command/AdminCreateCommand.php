<?php

declare(strict_types=1);

namespace Api\Admin\Command;

use Api\Admin\Entity\AdminRole;
use Api\Admin\InputFilter\CreateAdminInputFilter;
use Api\Admin\Service\AdminRoleService;
use Api\Admin\Service\AdminService;
use Api\App\Message;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AdminCreateCommand extends Command
{
    protected static $defaultName = 'admin:create';

    public function __construct(
        protected AdminService $adminService,
        protected AdminRoleService $adminRoleService
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
            ->addOption('lastName', 'l', InputOption::VALUE_REQUIRED, 'Admin last name')
        ;
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputFilter = (new CreateAdminInputFilter())->setData($this->getData($input));
        if (!$inputFilter->isValid()) {
            $messages = [];
            foreach ($inputFilter->getMessages() as $field => $errors) {
                foreach ($errors as $error) {
                    $messages[] = sprintf('%s: %s', $field, $error);
                }
            }

            throw new Exception(implode(PHP_EOL, $messages));
        }

        $this->adminService->createAdmin($inputFilter->getValues());

        $output->writeln(Message::ADMIN_CREATED);

        return Command::SUCCESS;
    }

    /**
     * @throws Exception
     */
    private function getData(InputInterface $input): array
    {
        $role = $this->adminRoleService->findOneBy(['name' => AdminRole::ROLE_ADMIN]);
        if (!($role instanceof AdminRole)) {
            throw new Exception(
                sprintf(Message::ADMIN_ROLE_MISSING, AdminRole::ROLE_ADMIN)
            );
        }

        return  [
            'identity' => $input->getOption('identity'),
            'password' => $input->getOption('password'),
            'passwordConfirm' => $input->getOption('password'),
            'firstName' => $input->getOption('firstName'),
            'lastName' => $input->getOption('lastName'),
            'roles' => [
                ['uuid' => $role->getUuid()->toString()]
            ]
        ];
    }
}
