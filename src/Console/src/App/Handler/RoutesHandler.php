<?php

declare(strict_types=1);

namespace Api\Console\App\Handler;

use Dot\AnnotatedServices\Annotation\Inject;
use Dot\AnnotatedServices\Annotation\Service;
use Dot\Console\Command\AbstractCommand;
use Laminas\Console\Adapter\AdapterInterface;
use Mezzio\Application;
use Laminas\Text\Table\Table;
use ZF\Console\Route;

use function array_values;
use function ksort;
use function strlen;

/**
 * Class RoutesHandler
 * @package Api\Console\App\Handler
 * @Service
 */
class RoutesHandler extends AbstractCommand
{
    /** @var Application $application */
    protected $application;

    /**
     * RoutesHandler constructor.
     * @param Application $application
     *
     * @Inject({Application::class})
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @param Route $route
     * @param AdapterInterface $console
     */
    public function __invoke(Route $route, AdapterInterface $console)
    {
        $columnWidths = ['method' => 6, 'name' => 4, 'path' => 4];

        $table = new Table(['columnWidths' => array_values($columnWidths)]);
        $table->setAutoSeparate(Table::AUTO_SEPARATE_HEADER);
        $table->setPadding(1);
        $table->appendRow(['Method', 'Name', 'Path']);

        $routes = [];
        foreach ($this->application->getRoutes() as $routeData) {
            foreach ($routeData->getAllowedMethods() as $method) {
                $routes[$routeData->getPath() . '-' . $method] = [
                    $method,
                    $routeData->getName(),
                    $routeData->getPath()
                ];
                if (strlen($routeData->getName()) > $columnWidths['name']) {
                    $columnWidths['name'] = strlen($routeData->getName());
                }
                if (strlen($routeData->getPath()) > $columnWidths['path']) {
                    $columnWidths['path'] = strlen($routeData->getPath());
                }
            }
        }

        ksort($routes);
        foreach ($routes as $routeData) {
            $table->appendRow($routeData);
        }

        $columnWidths['method'] += 2;
        $columnWidths['name'] += 2;
        $columnWidths['path'] += 2;
        $table->setColumnWidths(array_values($columnWidths));

        $console->write($table->render());
    }
}
