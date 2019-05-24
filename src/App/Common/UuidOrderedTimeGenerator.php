<?php

declare(strict_types=1);

namespace App\Common;

use Exception;
use Ramsey\Uuid\Codec\OrderedTimeCodec;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidInterface;

/**
 * Class UuidOrderedTimeGenerator
 * @package App\Common
 */
final class UuidOrderedTimeGenerator
{
    /** @var UuidFactory $factory */
    private static $factory;

    /**
     * @return UuidInterface
     */
    public static function generateUuid(): UuidInterface
    {
        try {
            return self::getFactory()->uuid1();
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * @return UuidFactory
     */
    private static function getFactory(): UuidFactory
    {
        if (!self::$factory) {
            self::$factory = clone Uuid::getFactory();

            $codec = new OrderedTimeCodec(
                self::$factory->getUuidBuilder()
            );

            self::$factory->setCodec($codec);
        }

        return self::$factory;
    }
}
