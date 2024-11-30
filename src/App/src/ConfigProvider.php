<?php

declare(strict_types=1);

namespace Api\App;

use Api\App\Command\RouteListCommand;
use Api\App\Command\TokenGenerateCommand;
use Api\App\Factory\AuthenticationMiddlewareFactory;
use Api\App\Factory\HandlerDelegatorFactory;
use Api\App\Factory\RouteListCommandFactory;
use Api\App\Factory\TokenGenerateCommandFactory;
use Api\App\Handler\ErrorReportHandler;
use Api\App\Middleware\AuthenticationMiddleware;
use Api\App\Middleware\AuthorizationMiddleware;
use Api\App\Middleware\ContentNegotiationMiddleware;
use Api\App\Middleware\DeprecationMiddleware;
use Api\App\Middleware\ErrorResponseMiddleware;
use Api\App\Service\ErrorReportService;
use Api\App\Service\ErrorReportServiceInterface;
use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Dot\Mail\Factory\MailOptionsAbstractFactory;
use Dot\Mail\Factory\MailServiceAbstractFactory;
use Dot\Mail\Service\MailService;
use Laminas\Hydrator\ArraySerializableHydrator;
use Mezzio\Application;
use Mezzio\Authentication;
use Mezzio\Hal\Metadata\MetadataMap;
use Mezzio\Hal\Metadata\RouteBasedCollectionMetadata;
use Mezzio\Hal\Metadata\RouteBasedResourceMetadata;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Twig\TwigEnvironmentFactory;
use Mezzio\Twig\TwigExtension;
use Mezzio\Twig\TwigExtensionFactory;
use Mezzio\Twig\TwigRenderer;
use Mezzio\Twig\TwigRendererFactory;
use Twig\Environment;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'     => $this->getDependencies(),
            MetadataMap::class => $this->getHalConfig(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class        => [RoutesDelegator::class],
                ErrorReportHandler::class => [HandlerDelegatorFactory::class],
            ],
            'factories'  => [
                'dot-mail.options.default'          => MailOptionsAbstractFactory::class,
                'dot-mail.service.default'          => MailServiceAbstractFactory::class,
                AuthenticationMiddleware::class     => AuthenticationMiddlewareFactory::class,
                AuthorizationMiddleware::class      => AttributedServiceFactory::class,
                ContentNegotiationMiddleware::class => AttributedServiceFactory::class,
                DeprecationMiddleware::class        => AttributedServiceFactory::class,
                Environment::class                  => TwigEnvironmentFactory::class,
                ErrorReportHandler::class           => AttributedServiceFactory::class,
                ErrorReportService::class           => AttributedServiceFactory::class,
                ErrorResponseMiddleware::class      => AttributedServiceFactory::class,
                RouteListCommand::class             => RouteListCommandFactory::class,
                TokenGenerateCommand::class         => TokenGenerateCommandFactory::class,
                TwigExtension::class                => TwigExtensionFactory::class,
                TwigRenderer::class                 => TwigRendererFactory::class,
            ],
            'aliases'    => [
                Authentication\AuthenticationInterface::class => Authentication\OAuth2\OAuth2Adapter::class,
                ErrorReportServiceInterface::class            => ErrorReportService::class,
                MailService::class                            => 'dot-mail.service.default',
                TemplateRendererInterface::class              => TwigRenderer::class,
            ],
        ];
    }

    public function getHalConfig(): array
    {
        return [];
    }

    public static function getCollection(string $collectionClass, string $route, string $collectionRelation): array
    {
        return [
            '__class__'           => RouteBasedCollectionMetadata::class,
            'collection_class'    => $collectionClass,
            'collection_relation' => $collectionRelation,
            'route'               => $route,
        ];
    }

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
