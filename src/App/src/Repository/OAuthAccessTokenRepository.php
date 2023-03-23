<?php

declare(strict_types=1);

namespace Api\App\Repository;

use Api\Admin\Entity\Admin;
use Api\App\Entity\OAuthAccessToken;
use Api\App\Entity\OAuthClient;
use Api\User\Entity\User;
use Doctrine\ORM\EntityRepository;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class OAuthAccessTokenRepository extends EntityRepository implements AccessTokenRepositoryInterface
{
    public function findAccessTokens(string $identifier): array
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['oauth_access_tokens'])
            ->from(OAuthAccessToken::class, 'oauth_access_tokens')
            ->andWhere('oauth_access_tokens.userId = :identifier')
            ->setParameter('identifier', $identifier)
            ->andWhere('oauth_access_tokens.isRevoked = 0')
            ->getQuery()
            ->getResult();
    }

    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        $userIdentifier = null
    ): OAuthAccessToken {
        $accessToken = (new OAuthAccessToken())->setClient($clientEntity);
        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }

        if (is_null($userIdentifier)) {
            return $accessToken;
        }

        if ($clientEntity->getName() === OAuthClient::NAME_ADMIN) {
            $repository = $this->getEntityManager()->getRepository(Admin::class);
        } else {
            $repository = $this->getEntityManager()->getRepository(User::class);
        }

        $user = $repository->findOneBy(['identity' => $userIdentifier]);
        if ($user instanceof UserEntityInterface) {
            $accessToken->setUserIdentifier($user->getIdentifier());
        }

        return $accessToken;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $this->getEntityManager()->persist($accessTokenEntity);
        $this->getEntityManager()->flush();
    }

    public function revokeAccessToken($tokenId): void
    {
        $accessTokenEntity = $this->findOneBy(['token' => $tokenId]);
        if ($accessTokenEntity instanceof OAuthAccessToken) {
            $this->getEntityManager()->persist($accessTokenEntity->revoke());
            $this->getEntityManager()->flush();
        }
    }

    public function isAccessTokenRevoked($tokenId): bool
    {
        $accessTokenEntity = $this->findOneBy(['token' => $tokenId]);
        if ($accessTokenEntity instanceof OAuthAccessToken) {
            return $accessTokenEntity->getIsRevoked();
        }

        return true;
    }
}
