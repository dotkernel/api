<?php

declare(strict_types=1);

namespace Api\App;

use Api\App\Common\Factory\AnnotationsCacheFactory;
use ContainerInteropDoctrine\EntityManagerFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Dot\AnnotatedServices\Factory\AbstractAnnotatedFactory;
use Dot\Mail\Factory\MailOptionsAbstractFactory;
use Dot\Mail\Factory\MailServiceAbstractFactory;
use Dot\Mail\Service\MailService;
use Twig\Environment;
use Zend\Expressive\Application;
use Zend\Expressive\Authentication;
use Zend\Expressive\Hal\Metadata\MetadataMap;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Expressive\Twig\TwigEnvironmentFactory;
use Zend\Expressive\Twig\TwigExtension;
use Zend\Expressive\Twig\TwigExtensionFactory;
use Zend\Expressive\Twig\TwigRenderer;
use Zend\Expressive\Twig\TwigRendererFactory;

/**
 * Class ConfigProvider
 * @package Api\App
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            MetadataMap::class => $this->getHalConfig(),
            'templates' => $this->getTemplates()
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies() : array
    {
        return [
            'delegators' => [
                Application::class => [
                    \Api\App\RoutesDelegator::class,
                    \Api\User\RoutesDelegator::class
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
    public function getHalConfig() : array
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
