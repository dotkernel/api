<?php

declare(strict_types=1);

namespace Api\App\Command;

use Api\App\Exception\NotFoundException;
use Api\App\Service\ErrorReportServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

#[AsCommand(
    name: 'token:generate',
    description: 'Generic token generator.',
)]
class TokenGenerateCommand extends Command
{
    /** @var string $defaultName */
    protected static $defaultName      = 'token:generate';
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
            ->setHelp(
            // @codingStandardsIgnoreStart
                <<<MSG
<info>%command.name%</info> is a multipurpose command that allows creating tokens required by different parts of the API.

Usage:
1. Create token for the error reporting endpoint:
* run: <info>%command.full_name% $this->typeErrorReporting</info>
* copy the generated token
* open <comment>config/autoload/error-handling.global.php</comment>
* paste the copied string inside the <comment>tokens</comment> array found under the <comment>ErrorReportServiceInterface::class</comment> key.
MSG
            // @codingStandardsIgnoreEnd
            );
    }

    /**
     * @throws NotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getArgument('type');
        match ($type) {
            $this->typeErrorReporting => $this->generateErrorReportingToken($output),
            default => throw new NotFoundException(
                sprintf('Unknown token type: %s', $type)
            )
        };

        return Command::SUCCESS;
    }

    private function generateErrorReportingToken(OutputInterface $output): void
    {
        $token = $this->errorReportService->generateToken();

        $output->writeln(
        // @codingStandardsIgnoreStart
            <<<MSG
Error reporting token:

    <info>$token</info>

* copy the generated token
* open <comment>config/autoload/error-handling.global.php</comment>
* paste the copied string inside the <comment>tokens</comment> array found under the <comment>ErrorReportServiceInterface::class</comment> key.
MSG
        // @codingStandardsIgnoreEnd
        );
    }
}
