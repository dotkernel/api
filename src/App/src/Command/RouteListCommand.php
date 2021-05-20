<?php

declare(strict_types=1);

namespace Api\App\Command;

use Api\App\RoutesDelegator;
use Mezzio\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RouteListCommand
 * @package Api\App\Command
 */
class RouteListCommand extends Command
{
    protected static $defaultName = 'route:list';

    private Application $application;

    /**
     * RouteListCommand constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        parent::__construct(self::$defaultName);
        $this->application = $application;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName(self::$defaultName)->setDescription('List application routes.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $routes = [];
        foreach ($this->application->getRoutes() as $route) {
            foreach ($route->getAllowedMethods() as $method) {
                $key = $route->getName() . '.' . $method;
                $routes[$key] = [
                    'name' => $route->getName(),
                    'path' => $route->getPath(),
                    'method' => $method,
                    'middleware' => get_class($route->getMiddleware()),
                ];
            }
        }
        ksort($routes);

        $table = new Table($output);
        $table
            ->setHeaders(['Method', 'Name', 'Path'])
            ->setRows(array_map(function ($route) {
            $path = str_replace(RoutesDelegator::REGEXP_UUID, '{uuid}', $route['path']);
            return [
                $route['method'],
                $route['name'],
                $path,
            ];
        }, $routes));
        $table->render();

        return 0;
    }
}
