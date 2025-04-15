<?php

declare(strict_types=1);

namespace Api\App\Attribute;

use Api\App\Exception\SunsetException;
use Core\App\Message;
use Laminas\Validator\Date;

readonly class BaseDeprecation
{
    public function __construct(
        public ?string $sunset = null,
        public ?string $link = null,
        public ?string $deprecationReason = null,
        public string $rel = 'sunset',
        public string $type = 'text/html',
    ) {
        if (null !== $sunset && ! (new Date())->isValid($sunset)) {
            throw new SunsetException(Message::invalidValue('sunset'));
        }
    }

    public function toArray(): array
    {
        return [
            'sunset'            => $this->sunset,
            'link'              => $this->link,
            'rel'               => $this->rel,
            'type'              => $this->type,
            'deprecationReason' => $this->deprecationReason,
            'deprecationType'   => static::class,
        ];
    }
}
