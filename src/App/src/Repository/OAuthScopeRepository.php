<?php


namespace Api\App\Repository;


use Api\App\Entity\OAuthScope;
use Doctrine\ORM\EntityRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

/**
 * Class OAuthScopeRepository
 * @package Api\App\Repository
 */
class OAuthScopeRepository extends EntityRepository implements ScopeRepositoryInterface
{
    public function getScopeEntityByIdentifier($identifier): ?ScopeEntityInterface
    {
        /** @var ?OAuthScope $scope */
        $scope = $this->findOneBy(['scope' => $identifier]);

        return $scope;
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
