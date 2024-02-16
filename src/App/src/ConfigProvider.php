<?php

declare(strict_types=1);

namespace Api\App;

use Api\App\Command\RouteListCommand;
use Api\App\Command\TokenGenerateCommand;
use Api\App\Entity\EntityListenerResolver;
use Api\App\Factory\AuthenticationMiddlewareFactory;
use Api\App\Factory\EntityListenerResolverFactory;
use Api\App\Factory\RouteListCommandFactory;
use Api\App\Factory\TokenGenerateCommandFactory;
use Api\App\Middleware\AuthenticationMiddleware;
use Api\App\Middleware\AuthorizationMiddleware;
use Api\App\Middleware\ErrorResponseMiddleware;
use Api\App\Service\ErrorReportService;
use Api\App\Service\ErrorReportServiceInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Dot\AnnotatedServices\Factory\AnnotatedServiceFactory;
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
use Roave\PsrContainerDoctrine\EntityManagerFactory;
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
                Application::class => [
                    RoutesDelegator::class,
                    \Api\Admin\RoutesDelegator::class,
                    \Api\User\RoutesDelegator::class,
                ],
            ],
            'factories'  => [
                'doctrine.entity_manager.orm_default' => EntityManagerFactory::class,
                'dot-mail.options.default'            => MailOptionsAbstractFactory::class,
                'dot-mail.service.default'            => MailServiceAbstractFactory::class,
                AuthenticationMiddleware::class       => AuthenticationMiddlewareFactory::class,
                AuthorizationMiddleware::class        => AnnotatedServiceFactory::class,
                Environment::class                    => TwigEnvironmentFactory::class,
                TwigExtension::class                  => TwigExtensionFactory::class,
                TwigRenderer::class                   => TwigRendererFactory::class,
                ErrorResponseMiddleware::class        => AnnotatedServiceFactory::class,
                RouteListCommand::class               => RouteListCommandFactory::class,
                TokenGenerateCommand::class           => TokenGenerateCommandFactory::class,
                ErrorReportService::class             => AnnotatedServiceFactory::class,
                EntityListenerResolver::class         => EntityListenerResolverFactory::class,
            ],
            'aliases'    => [
                Authentication\AuthenticationInterface::class => Authentication\OAuth2\OAuth2Adapter::class,
                MailService::class                            => 'dot-mail.service.default',
                EntityManager::class                          => 'doctrine.entity_manager.orm_default',
                EntityManagerInterface::class                 => 'doctrine.entity_manager.orm_default',
                TemplateRendererInterface::class              => TwigRenderer::class,
                ErrorReportServiceInterface::class            => ErrorReportService::class,
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
