<?php

declare(strict_types=1);

namespace Api\App\Attribute;

use Api\App\Exception\DeprecationSunsetException;
use Api\App\Message;
use Attribute;
use Laminas\Validator\Date;

use function sprintf;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ResourceDeprecation
{
    public function __construct(public string $sunset, public string $link, public string $deprecationReason = '')
    {
        if (! (new Date())->isValid($sunset)) {
            throw new DeprecationSunsetException(sprintf(Message::INVALID_VALUE, 'sunset'));
        }
    }
}
