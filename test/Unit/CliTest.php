<?php

declare(strict_types=1);

namespace ApiTest\Unit;

use Dot\Cli\Application;
use Dot\Cli\FileLockerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Tester\CommandTester;

class CliTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testWillListCommandsWhenNoCommandSpecified(): void
    {
        $application = new Application(
            $this->createMock(FileLockerInterface::class),
            []
        );
        $application->setAutoExit(false);

        $applicationTester = new ApplicationTester($application);
        $applicationTester->run([]);

        $output = $applicationTester->getDisplay();

        $this->assertSame(Command::SUCCESS, $applicationTester->getStatusCode());
        $this->assertStringContainsString('Available commands:', $output);
        $this->assertStringContainsString('List commands', $output);
    }

    /**
     * @throws Exception
     */
    public function testWillListCommands(): void
    {
        $application = new Application(
            $this->createMock(FileLockerInterface::class),
            []
        );

        $command = $application->find('list');

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('Available commands:', $output);
        $this->assertStringContainsString('List commands', $output);
    }
}
