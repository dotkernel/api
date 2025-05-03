<?php

declare(strict_types=1);

namespace Api\App;

use Api\App\Command\TokenGenerateCommand;
use Api\App\Factory\HandlerDelegatorFactory;
use Api\App\Factory\ProblemDetailsDelegatorFactory;
use Api\App\Handler\GetIndexResourceHandler;
use Api\App\Handler\PostErrorReportResourceHandler;
use Api\App\Middleware\AuthenticationMiddleware;
use Api\App\Middleware\AuthorizationMiddleware;
use Api\App\Middleware\ContentNegotiationMiddleware;
use Api\App\Middleware\DeprecationMiddleware;
use Api\App\Middleware\ErrorReportPermissionMiddleware;
use Api\App\Middleware\ResourceProviderMiddleware;
use Api\App\Service\ErrorReportService;
use Api\App\Service\ErrorReportServiceInterface;
use Api\App\Template\Parser;
use Api\App\Template\ParserInterface;
use Api\App\Template\Renderer;
use Api\App\Template\RendererInterface;
use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Laminas\Hydrator\ArraySerializableHydrator;
use Mezzio\Application;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\OAuth2\OAuth2Adapter;
use Mezzio\Hal\Metadata\RouteBasedCollectionMetadata;
use Mezzio\Hal\Metadata\RouteBasedResourceMetadata;
use Mezzio\ProblemDetails\ProblemDetailsMiddleware;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    private function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class                    => [RoutesDelegator::class],
                GetIndexResourceHandler::class        => [HandlerDelegatorFactory::class],
                PostErrorReportResourceHandler::class => [HandlerDelegatorFactory::class],
                ProblemDetailsMiddleware::class       => [ProblemDetailsDelegatorFactory::class],
            ],
            'factories'  => [
                AuthenticationMiddleware::class        => AttributedServiceFactory::class,
                AuthorizationMiddleware::class         => AttributedServiceFactory::class,
                ContentNegotiationMiddleware::class    => AttributedServiceFactory::class,
                DeprecationMiddleware::class           => AttributedServiceFactory::class,
                ErrorReportPermissionMiddleware::class => AttributedServiceFactory::class,
                ResourceProviderMiddleware::class      => AttributedServiceFactory::class,
                GetIndexResourceHandler::class         => AttributedServiceFactory::class,
                PostErrorReportResourceHandler::class  => AttributedServiceFactory::class,
                ErrorReportService::class              => AttributedServiceFactory::class,
                TokenGenerateCommand::class            => AttributedServiceFactory::class,
                Parser::class                          => AttributedServiceFactory::class,
                Renderer::class                        => AttributedServiceFactory::class,
            ],
            'aliases'    => [
                AuthenticationInterface::class     => OAuth2Adapter::class,
                ErrorReportServiceInterface::class => ErrorReportService::class,
                RendererInterface::class           => Renderer::class,
                ParserInterface::class             => Parser::class,
            ],
        ];
    }

    /**
     * @param class-string $collectionClass
     */
    public static function getCollection(string $collectionClass, string $route, string $collectionRelation): array
    {
        return [
            '__class__'           => RouteBasedCollectionMetadata::class,
            'collection_class'    => $collectionClass,
            'collection_relation' => $collectionRelation,
            'route'               => $route,
        ];
    }

    /**
     * @param class-string $resourceClass
     */
    public static function getResource(
        string $resourceClass,
        string $route,
        string $resourceIdentifier = 'uuid',
        string $resourceIdentifierPlaceholder = 'uuid'
    ): array {
        return [
            '__class__'                    => RouteBasedResourceMetadata::class,
            'resource_class'               => $resourceClass,
            'route'                        => $route,
            'extractor'                    => ArraySerializableHydrator::class,
            'resource_identifier'          => $resourceIdentifier,
            'route_identifier_placeholder' => $resourceIdentifierPlaceholder,
        ];
    }
}
