<?php

declare(strict_types=1);

namespace Api\App\Repository;

use Api\App\Entity\OAuthRefreshToken;
use Doctrine\ORM\EntityRepository;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class OAuthRefreshTokenRepository extends EntityRepository implements RefreshTokenRepositoryInterface
{
    public function getNewRefreshToken(): OAuthRefreshToken
    {
        return new OAuthRefreshToken();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        $this->getEntityManager()->persist($refreshTokenEntity);
        $this->getEntityManager()->flush();
    }

    public function revokeRefreshToken($tokenId): void
    {
        $refreshTokenEntity = $this->find($tokenId);
        if ($refreshTokenEntity instanceof OAuthRefreshToken) {
            $this->getEntityManager()->persist($refreshTokenEntity->revoke());
            $this->getEntityManager()->flush();
        }
    }

    public function isRefreshTokenRevoked($tokenId): bool
    {
        $refreshTokenEntity = $this->find($tokenId);
        if ($refreshTokenEntity instanceof OAuthRefreshToken) {
            return $refreshTokenEntity->getIsRevoked();
        }

        return true;
    }
}
