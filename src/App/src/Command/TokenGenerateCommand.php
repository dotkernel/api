<?php

declare(strict_types=1);

namespace Api\App\Command;

use Api\App\Service\ErrorReportServiceInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TokenGenerateCommand extends Command
{
    protected static $defaultName = 'token:generate';
    private string $typeErrorReporting = 'error-reporting';

    public function __construct(
        protected ErrorReportServiceInterface $errorReportService
    ) {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Generic token generator.')
            ->addArgument('type', InputArgument::REQUIRED, 'The type of token to be generated.')
            ->addUsage($this->typeErrorReporting)
            ->setHelp(<<<MSG
<info>%command.name%</info> is a multipurpose command that allows creating tokens required by different parts of the API.

Usage:
1. Create token for the error reporting endpoint:
* run: <info>%command.full_name% $this->typeErrorReporting</info>
* copy the generated token
* open <comment>config/autoload/error-handling.global.php</comment>
* paste the copied string inside the <comment>tokens</comment> array found under the <comment>ErrorReportServiceInterface::class</comment> key.
MSG
            );
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getArgument('type');
        match($type) {
            $this->typeErrorReporting => $this->generateErrorReportingToken($output),
            default => throw new Exception(
                sprintf('Unknown token type: %s', $type)
            )
        };

        return Command::SUCCESS;
    }

    private function generateErrorReportingToken(OutputInterface $output): void
    {
        $token = $this->errorReportService->generateToken();

        $output->writeln(<<<MSG
Error reporting token:

    <info>$token</info>

* copy the generated token
* open <comment>config/autoload/error-handling.global.php</comment>
* paste the copied string inside the <comment>tokens</comment> array found under the <comment>ErrorReportServiceInterface::class</comment> key.
MSG
);
    }
}
