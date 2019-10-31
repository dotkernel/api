<?php

declare(strict_types=1);

namespace Api\App\MailChimp\Factory;

use DrewM\MailChimp\MailChimp;
use Exception;
use Psr\Container\ContainerInterface;

/**
 * Class MailChimpFactory
 * @package Api\App\MailChimp\Factory
 */
class MailChimpFactory
{
    /**
     * @param ContainerInterface $container
     * @return MailChimp
     * @throws Exception
     */
    public function __invoke(ContainerInterface $container) : MailChimp
    {
        $config = $container->get('config')['mailChimp'] ?? [];

        return new MailChimp($config['apiKey']);
    }
}
