<?php

declare(strict_types=1);

namespace Api\App\Repository;

use Doctrine\ORM\EntityRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

/**
 * @extends EntityRepository<object>
 */
class OAuthScopeRepository extends EntityRepository implements ScopeRepositoryInterface
{
    /**
     * @param string $identifier
     */
    public function getScopeEntityByIdentifier($identifier): ?ScopeEntityInterface
    {
        return $this->findOneBy(['scope' => $identifier]);
    }

    /**
     * @param string $grantType
     * @param null|string $userIdentifier
     * @return ScopeEntityInterface[]
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ): array {
        return $scopes;
    }
}
