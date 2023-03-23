<?php

declare(strict_types=1);

namespace Api\App\Entity;

use Ramsey\Uuid\Codec\OrderedTimeCodec;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidInterface;
use Throwable;

final class UuidOrderedTimeGenerator
{
    private static ?UuidFactory $factory = null;

    public static function generateUuid(): ?UuidInterface
    {
        try {
            return self::getFactory()->uuid1();
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
        }

        return null;
    }

    private static function getFactory(): ?UuidFactory
    {
        if (!(self::$factory instanceof UuidFactory)) {
            /** @var UuidFactory $factory */
            $factory = clone Uuid::getFactory();
            self::$factory = $factory;

            $codec = new OrderedTimeCodec(
                self::$factory->getUuidBuilder()
            );

            self::$factory->setCodec($codec);
        }

        return self::$factory;
    }
}
