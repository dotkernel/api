<?php

declare(strict_types=1);

namespace Api\App;

use Api\App\Common\Factory\AnnotationsCacheFactory;
use Api\App\Common\Factory\ErrorResponseMiddlewareFactory;
use Api\App\Common\Middleware\ErrorResponseMiddleware;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Dot\AnnotatedServices\Factory\AbstractAnnotatedFactory;
use Dot\Mail\Factory\MailOptionsAbstractFactory;
use Dot\Mail\Factory\MailServiceAbstractFactory;
use Dot\Mail\Service\MailService;
use Roave\PsrContainerDoctrine\EntityManagerFactory;
use Twig\Environment;
use Mezzio\Application;
use Mezzio\Authentication;
use Mezzio\Hal\Metadata\MetadataMap;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Twig\TwigEnvironmentFactory;
use Mezzio\Twig\TwigExtension;
use Mezzio\Twig\TwigExtensionFactory;
use Mezzio\Twig\TwigRenderer;
use Mezzio\Twig\TwigRendererFactory;

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
            MetadataMap::class => $this->getHalConfig(),
            'templates' => $this->getTemplates()
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
                    \Api\App\RoutesDelegator::class,
                    \Api\User\RoutesDelegator::class,
                ]
            ],
            'factories'  => [
                'doctrine.entity_manager.orm_default' => EntityManagerFactory::class,
                'dot-mail.options.default' => MailOptionsAbstractFactory::class,
                'dot-mail.service.default' => MailServiceAbstractFactory::class,
                AbstractAnnotatedFactory::CACHE_SERVICE => AnnotationsCacheFactory::class,
                Environment::class => TwigEnvironmentFactory::class,
                TwigExtension::class => TwigExtensionFactory::class,
                TwigRenderer::class => TwigRendererFactory::class,
                ErrorResponseMiddleware::class => ErrorResponseMiddlewareFactory::class,
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
     * @return array
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'app' => [__DIR__ . '/../templates/app'],
                'error' => [__DIR__ . '/../templates/error'],
                'layout' => [__DIR__ . '/../templates/layout'],
                'page' => [__DIR__ . '/../templates/page'],
                'partial' => [__DIR__ . '/../templates/partial'],
            ]
        ];
    }
}
