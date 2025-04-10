<?php

declare(strict_types=1);

namespace Core\App\Command;

use Core\App\ConfigProvider;
use Dot\DependencyInjection\Attribute\Inject;
use Fig\Http\Message\RequestMethodInterface;
use Mezzio\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function count;
use function ksort;
use function sprintf;
use function str_contains;
use function str_pad;
use function str_replace;

use const STR_PAD_LEFT;

#[AsCommand(
    name: 'route:list',
    description: 'List application routes',
)]
class RouteListCommand extends Command
{
    /** @var string $defaultName */
    protected static $defaultName = 'route:list';

    #[Inject(
        Application::class,
    )]
    public function __construct(
        protected Application $application,
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
            $methods = $route->getAllowedMethods();
            if (empty($methods)) {
                $methods = [
                    RequestMethodInterface::METHOD_DELETE,
                    RequestMethodInterface::METHOD_GET,
                    RequestMethodInterface::METHOD_PATCH,
                    RequestMethodInterface::METHOD_POST,
                    RequestMethodInterface::METHOD_PUT,
                ];
            }

            foreach ($methods as $method) {
                if (! str_contains($route->getName(), $nameFilter)) {
                    continue;
                }
                if (! str_contains($route->getPath(), $pathFilter)) {
                    continue;
                }
                if (! str_contains($method, $methodFilter)) {
                    continue;
                }

                $routes[sprintf('%s-%s', $route->getPath(), $method)] = [
                    'name'   => $route->getName(),
                    'path'   => $route->getPath(),
                    'method' => $method,
                ];
            }
        }
        ksort($routes);

        $index = 1;
        $table = (new Table($output))
            ->setHeaders(['   #', 'Request method', 'Route name', 'Route path'])
            ->setHeaderTitle(sprintf('%d Routes', count($routes)));
        foreach ($routes as $route) {
            $table->addRow([
                str_pad((string) $index++, 4, ' ', STR_PAD_LEFT),
                $route['method'],
                $route['name'],
                str_replace(ConfigProvider::REGEXP_UUID, '{uuid}', $route['path']),
            ]);
        }
        $table->render();

        return Command::SUCCESS;
    }
}
