<?php

namespace AppTest\Helper;

/**
 * Class TestHelper
 * @package AppTest\Helper
 */
class TestHelper
{
    private const TEST_MODE = 'TEST_MODE';

    public static function enableTestMode(): void
    {
        putenv(sprintf('%s=true', self::TEST_MODE));
    }

    public static function disableTestMode(): void
    {
        putenv(self::TEST_MODE);
    }

    public static function isTestMode(): bool
    {
        return getenv(self::TEST_MODE) === 'true';
    }
}
