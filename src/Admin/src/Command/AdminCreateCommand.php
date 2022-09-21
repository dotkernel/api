<?php

declare(strict_types=1);

namespace Api\Admin\Command;

use Api\Admin\Entity\AdminRole;
use Api\Admin\Form\InputFilter\CreateAdminInputFilter;
use Api\Admin\Service\AdminRoleService;
use Api\Admin\Service\AdminService;
use Api\App\Message;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Class AdminCreateCommand
 * @package Api\Admin\Command
 */
class AdminCreateCommand extends Command
{
    protected static $defaultName = 'admin:create';

    private AdminService $adminService;
    private AdminRoleService $adminRoleService;

    /**
     * AdminCreateCommand constructor.
     * @param AdminService $adminService
     * @param AdminRoleService $adminRoleService
     */
    public function __construct(AdminService $adminService, AdminRoleService $adminRoleService)
    {
        parent::__construct(self::$defaultName);
        $this->adminService = $adminService;
        $this->adminRoleService = $adminRoleService;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Create admin account.')
            ->addOption('identity', 'i', InputOption::VALUE_REQUIRED, 'Admin account identity')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Admin account password')
            ->addOption('firstName', 'f', InputOption::VALUE_OPTIONAL, 'Admin account firstname')
            ->addOption('lastName', 'l', InputOption::VALUE_OPTIONAL, 'Admin account lastname')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $role = $this->adminRoleService->getAdminRole();
        if (!($role instanceof AdminRole)) {
            throw new Exception(
                sprintf(Message::ADMIN_ROLE_MISSING, AdminRole::ROLE_ADMIN)
            );
        }
        $data = [
            'identity' => $input->getOption('identity'),
            'password' => $input->getOption('password'),
            'passwordConfirm' => $input->getOption('password'),
            'firstName' => $input->getOption('firstName'),
            'lastName' => $input->getOption('lastName'),
            'roles' => [
                ['uuid' => $role->getUuid()->toString()]
            ]
        ];

        $inputFilter = (new CreateAdminInputFilter())->getInputFilter();
        $inputFilter->setData($data);
        if (!$inputFilter->isValid()) {
            $messages = [];
            foreach ($inputFilter->getMessages() as $field => $errors) {
                foreach ((array)$errors as $error) {
                    $messages[] = sprintf('%s: %s', $field, $error);
                }
            }
            throw new Exception(implode(PHP_EOL, $messages));
        }

        $this->adminService->createAdmin($inputFilter->getValues());
        $output->writeln(Message::ADMIN_CREATED);

        return 0;
    }
}
