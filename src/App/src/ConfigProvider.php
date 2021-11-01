<?php

declare(strict_types=1);

namespace Api\App;

use Api\App\Command\RouteListCommand;
use Api\App\Factory\AnnotationsCacheFactory;
use Api\App\Factory\AuthenticationMiddlewareFactory;
use Api\App\Factory\RouteListCommandFactory;
use Api\App\Middleware\AuthenticationMiddleware;
use Api\App\Middleware\AuthorizationMiddleware;
use Api\App\Middleware\ErrorResponseMiddleware;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Dot\AnnotatedServices\Factory\AbstractAnnotatedFactory;
use Dot\AnnotatedServices\Factory\AnnotatedServiceFactory;
use Dot\Mail\Factory\MailOptionsAbstractFactory;
use Dot\Mail\Factory\MailServiceAbstractFactory;
use Dot\Mail\Service\MailService;
use Laminas\Hydrator\ArraySerializableHydrator;
use Mezzio\Application;
use Mezzio\Authentication;
use Mezzio\Hal\Metadata\RouteBasedCollectionMetadata;
use Mezzio\Hal\Metadata\RouteBasedResourceMetadata;
use Mezzio\Hal\Metadata\MetadataMap;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Twig\TwigEnvironmentFactory;
use Mezzio\Twig\TwigExtension;
use Mezzio\Twig\TwigExtensionFactory;
use Mezzio\Twig\TwigRenderer;
use Mezzio\Twig\TwigRendererFactory;
use Roave\PsrContainerDoctrine\EntityManagerFactory;
use Twig\Environment;

/**
 * Class ConfigProvider
 * @package Api\App
 */
class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            MetadataMap::class => $this->getHalConfig()
        ];
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class => [
                    RoutesDelegator::class,
                    \Api\Admin\RoutesDelegator::class,
                    \Api\User\RoutesDelegator::class,
                ]
            ],
            'factories'  => [
                'doctrine.entity_manager.orm_default' => EntityManagerFactory::class,
                'dot-mail.options.default' => MailOptionsAbstractFactory::class,
                'dot-mail.service.default' => MailServiceAbstractFactory::class,
                AbstractAnnotatedFactory::CACHE_SERVICE => AnnotationsCacheFactory::class,
                AuthenticationMiddleware::class => AuthenticationMiddlewareFactory::class,
                AuthorizationMiddleware::class => AnnotatedServiceFactory::class,
                Environment::class => TwigEnvironmentFactory::class,
                TwigExtension::class => TwigExtensionFactory::class,
                TwigRenderer::class => TwigRendererFactory::class,
                ErrorResponseMiddleware::class => AnnotatedServiceFactory::class,
                RouteListCommand::class => RouteListCommandFactory::class
            ],
            'aliases' => [
                Authentication\AuthenticationInterface::class => Authentication\OAuth2\OAuth2Adapter::class,
                MailService::class => 'dot-mail.service.default',
                EntityManager::class => 'doctrine.entity_manager.orm_default',
                EntityManagerInterface::class => 'doctrine.entity_manager.default',
                TemplateRendererInterface::class => TwigRenderer::class,
            ]
        ];
    }

    /**
     * @return array
     */
    public function getHalConfig(): array
    {
        return [];
    }

    /**
     * @param string $collectionClass
     * @param string $route
     * @param string $collectionRelation
     * @return string[]
     */
    public static function getCollection(
        string $collectionClass,
        string $route,
        string $collectionRelation
    ): array
    {
        return [
            '__class__' => RouteBasedCollectionMetadata::class,
            'collection_class' => $collectionClass,
            'collection_relation' => $collectionRelation,
            'route' => $route
        ];
    }

    /**
     * @param string $resourceClass
     * @param string $route
     * @param string $resourceIdentifier
     * @param string $resourceIdentifierPlaceholder
     * @return string[]
     */
    public static function getResource(
        string $resourceClass,
        string $route,
        string $resourceIdentifier = 'uuid',
        string $resourceIdentifierPlaceholder = 'uuid'
    ): array
    {
        return [
            '__class__' => RouteBasedResourceMetadata::class,
            'resource_class' => $resourceClass,
            'route' => $route,
            'extractor' => ArraySerializableHydrator::class,
            'resource_identifier' => $resourceIdentifier,
            'route_identifier_placeholder' => $resourceIdentifierPlaceholder
        ];
    }
}
