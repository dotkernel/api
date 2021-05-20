<?php

declare(strict_types=1);

namespace Api\App\Entity;

use Ramsey\Uuid\Codec\OrderedTimeCodec;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidInterface;
use Throwable;

/**
 * Class UuidOrderedTimeGenerator
 * @package Api\App\Entity
 */
final class UuidOrderedTimeGenerator
{
    /** @var UuidFactory $factory */
    private static $factory;

    /**
     * @return UuidInterface|null
     */
    public static function generateUuid(): ?UuidInterface
    {
        try {
            return self::getFactory()->uuid1();
        } catch (Throwable $exception) {}

        return null;
    }

    /**
     * @return UuidFactory|null
     */
    private static function getFactory(): ?UuidFactory
    {
        if (!(self::$factory instanceof UuidFactory)) {
            self::$factory = clone Uuid::getFactory();

            $codec = new OrderedTimeCodec(
                self::$factory->getUuidBuilder()
            );

            self::$factory->setCodec($codec);
        }

        return self::$factory;
    }
}
