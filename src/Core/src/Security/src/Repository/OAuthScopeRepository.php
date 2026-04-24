<?php

declare(strict_types=1);

namespace Core\Security\Repository;

use Core\App\Repository\AbstractRepository;
use Core\Security\Entity\OAuthScope;
use Dot\DependencyInjection\Attribute\Entity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

#[Entity(name: OAuthScope::class)]
class OAuthScopeRepository extends AbstractRepository implements ScopeRepositoryInterface
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

    public function finalizeScopes(
        array $scopes,
        string $grantType,
        ClientEntityInterface $clientEntity,
        string|null $userIdentifier = null,
        ?string $authCodeId = null
    ): array {
        return $scopes;
    }
}
