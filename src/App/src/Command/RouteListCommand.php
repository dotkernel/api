<?php

declare(strict_types=1);

namespace Api\App\Command;

use Api\App\RoutesDelegator;
use Mezzio\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function array_map;
use function ksort;
use function sprintf;
use function str_contains;
use function str_replace;

#[AsCommand(
    name: 'route:list',
    description: 'List API routes',
)]
class RouteListCommand extends Command
{
    /** @var string $defaultName */
    protected static $defaultName = 'route:list';

    public function __construct(
        protected Application $application
    ) {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('List application routes.')
            ->addUsage('[-i|--name[=NAME]] [-p|--path[=PATH]] [-m|--method[=METHOD]]')
            ->addOption('name', 'i', InputOption::VALUE_OPTIONAL, 'Filter routes by name')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'Filter routes by path')
            ->addOption('method', 'm', InputOption::VALUE_OPTIONAL, 'Filter routes by method');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nameFilter   = (string) $input->getOption('name');
        $pathFilter   = (string) $input->getOption('path');
        $methodFilter = (string) $input->getOption('method');

        $routes = [];
        foreach ($this->application->getRoutes() as $route) {
            foreach ($route->getAllowedMethods() as $method) {
                if (! str_contains($route->getName(), $nameFilter)) {
                    continue;
                }
                if (! str_contains($route->getPath(), $pathFilter)) {
                    continue;
                }
                if (! str_contains($method, $methodFilter)) {
                    continue;
                }

                $routes[sprintf('%s:%s', $route->getName(), $method)] = [
                    'name'   => $route->getName(),
                    'path'   => $route->getPath(),
                    'method' => $method,
                ];
            }
        }
        ksort($routes);

        (new Table($output))
            ->setHeaders(['Method', 'Name', 'Path'])
            ->setRows(array_map(function ($route) {
                $path = str_replace(RoutesDelegator::REGEXP_UUID, '{uuid}', $route['path']);
                return [
                    $route['method'],
                    $route['name'],
                    $path,
                ];
            }, $routes))
            ->render();

        return Command::SUCCESS;
    }
}
