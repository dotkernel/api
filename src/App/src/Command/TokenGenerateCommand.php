<?php

declare(strict_types=1);

namespace Api\App\Command;

use Api\App\Service\ErrorReportServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;

use const PHP_EOL;

#[AsCommand(
    name: 'token:generate',
    description: 'Generic token generator.',
)]
class TokenGenerateCommand extends Command
{
    /** @var string $defaultName */
    public static $defaultName         = 'token:generate';
    private string $typeErrorReporting = 'error-reporting';

    #[Inject(
        ErrorReportServiceInterface::class,
    )]
    public function __construct(
        protected ErrorReportServiceInterface $errorReportService,
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
                sprintf(
                    '<info>%%command.name%%</info> is a multipurpose command'
                    . ' that allows creating tokens required by different parts of the API.

Usage:
- Create token for the error reporting endpoint: <info>./%%command.full_name%% %s</info>',
                    $this->typeErrorReporting
                )
            );
    }

    /**
     * @throws RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getArgument('type');
        match ($type) {
            $this->typeErrorReporting => $this->generateErrorReportingToken(new SymfonyStyle($input, $output)),
            default => throw new RuntimeException(
                sprintf('Unknown token type: %s', $type)
            )
        };

        return Command::SUCCESS;
    }

    private function generateErrorReportingToken(SymfonyStyle $io): void
    {
        $token = $this->errorReportService->generateToken();

        $io->writeln('Error reporting token:' . PHP_EOL);
        $io->writeln('<info>    ' . $token . '</info>' . PHP_EOL);

        $io->writeln(
            sprintf(
                '* copy the generated token
* open <comment>config/autoload/error-handling.global.php</comment>
* paste the copied string inside the <comment>tokens</comment> array found under the <comment>%s</comment> key.',
                'ErrorReportServiceInterface::class'
            )
        );
    }
}
