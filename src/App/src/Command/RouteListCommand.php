<?php

declare(strict_types=1);

namespace Api\App\Command;

use Api\App\RoutesDelegator;
use Mezzio\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RouteListCommand extends Command
{
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
            ->addOption('method', 'm', InputOption::VALUE_OPTIONAL, 'Filter routes by method')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nameFilter = (string)$input->getOption('name');
        $pathFilter = (string)$input->getOption('path');
        $methodFilter = (string)$input->getOption('method');

        $routes = [];
        foreach ($this->application->getRoutes() as $route) {
            foreach ($route->getAllowedMethods() as $method) {
                if (stripos($route->getName(), $nameFilter) === false) {
                    continue;
                }
                if (stripos($route->getPath(), $pathFilter) === false) {
                    continue;
                }
                if (stripos($method, $methodFilter) === false) {
                    continue;
                }

                $routes[sprintf('%s:%s', $method, $route->getPath())] = [
                    'name' => $route->getName(),
                    'path' => $route->getPath(),
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
