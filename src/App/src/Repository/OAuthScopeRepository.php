<?php

declare(strict_types=1);

namespace Api\App\Repository;

use Doctrine\ORM\EntityRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class OAuthScopeRepository extends EntityRepository implements ScopeRepositoryInterface
{
    public function getScopeEntityByIdentifier($identifier): ?ScopeEntityInterface
    {
        return $this->findOneBy(['scope' => $identifier]);
    }

    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ): array {
        return $scopes;
    }
}
