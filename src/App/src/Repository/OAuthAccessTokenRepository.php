<?php

namespace Api\App\Repository;

use Api\Admin\Entity\Admin;
use Api\App\Entity\OAuthClient;
use Api\User\Entity\User;
use Doctrine\ORM\EntityRepository;
use Api\App\Entity\OAuthAccessToken;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

/**
 * Class OAuthAccessTokenRepository
 * @package Api\App\Repository
 */
class OAuthAccessTokenRepository extends EntityRepository implements AccessTokenRepositoryInterface
{
    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        $userIdentifier = null
    ): OAuthAccessToken
    {
        $accessToken = new OAuthAccessToken();
        $accessToken->setClient($clientEntity);

        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }

        if (null === $userIdentifier) {
            return $accessToken;
        }

        $repository = $this->getEntityManager()->getRepository(User::class);
        if ($clientEntity->getName() === OAuthClient::NAME_ADMIN) {
            $repository = $this->getEntityManager()->getRepository(Admin::class);
        }

        $user = $repository->findOneBy(['identity' => $userIdentifier]);
        if ($user instanceof UserEntityInterface) {
            $accessToken->setUserIdentifier($user->getIdentifier());
        }

        return $accessToken;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $this->getEntityManager()->persist($accessTokenEntity);
        $this->getEntityManager()->flush();
    }

    public function revokeAccessToken($tokenId)
    {
        /** @var ?OAuthAccessToken $accessTokenEntity */
        $accessTokenEntity = $this->findOneBy(['token' => $tokenId]);
        if (null === $accessTokenEntity) {
            return;
        }
        $accessTokenEntity->setIsRevoked(true);
        $this->getEntityManager()->persist($accessTokenEntity);
        $this->getEntityManager()->flush();
    }

    public function isAccessTokenRevoked($tokenId): bool
    {
        $accessTokenEntity = $this->findOneBy(['token' => $tokenId]);
        if (null === $accessTokenEntity) {
            return true;
        }

        return $accessTokenEntity->getIsRevoked();
    }
}
