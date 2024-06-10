<?php

declare(strict_types=1);

namespace Api\App\Repository;

use Api\App\Entity\OAuthScope;
use Doctrine\ORM\EntityRepository;
use Dot\DependencyInjection\Attribute\Entity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

/**
 * @extends EntityRepository<object>
 */
#[Entity(name: OAuthScope::class)]
class OAuthScopeRepository extends EntityRepository implements ScopeRepositoryInterface
{
    /**
     * @param string $identifier
     */
    public function getScopeEntityByIdentifier($identifier): ?ScopeEntityInterface
    {
        $scope = $this->findOneBy(['scope' => $identifier]);
        if ($scope instanceof OAuthScope) {
            return $scope;
        }

        return null;
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
