<?php

declare(strict_types=1);

namespace Api\Console;

/**
 * Class ConfigProvider
 * @package Api\Console
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
        return [];
    }
}
