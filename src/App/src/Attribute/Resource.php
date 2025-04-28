<?php

declare(strict_types=1);

namespace Api\App\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
readonly class Resource implements ResourceInterface
{
    /**
     * @param class-string $entity The target entity to be found
     * @param string $identifier The class property used to find the entity by
     * @param string $placeholder The route parameter containing the identifier value
     * @param class-string|null $guard An invokable class implementing Api\App\Guard\ResourceGuardInterface,
     * which will determine if the current user is allowed to access the entity
     */
    public function __construct(
        public string $entity,
        public string $identifier = 'uuid',
        public string $placeholder = 'uuid',
        public ?string $guard = null,
    ) {
    }

    public function hasGuard(): bool
    {
        return $this->guard !== null;
    }
}
