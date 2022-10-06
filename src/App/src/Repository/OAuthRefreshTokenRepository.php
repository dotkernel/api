<?php

namespace Api\App\Repository;

use Api\App\Entity\OAuthRefreshToken;
use Doctrine\ORM\EntityRepository;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

/**
 * Class OAuthRefreshTokenRepository
 * @package Api\App\Repository
 */
class OAuthRefreshTokenRepository extends EntityRepository implements RefreshTokenRepositoryInterface
{
    public function getNewRefreshToken(): OAuthRefreshToken
    {
        return new OAuthRefreshToken();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        $em = $this->getEntityManager();
        $em->persist($refreshTokenEntity);
        $em->flush();
    }

    public function revokeRefreshToken($tokenId): void
    {
        /** @var ?OAuthRefreshToken $refreshTokenEntity */
        $refreshTokenEntity = $this->find($tokenId);

        if (null === $refreshTokenEntity) {
            return;
        }

        $refreshTokenEntity->setIsRevoked(true);
        $em = $this->getEntityManager();
        $em->persist($refreshTokenEntity);
        $em->flush();
    }

    public function isRefreshTokenRevoked($tokenId): bool
    {
        /** @var ?OAuthRefreshToken $refreshTokenEntity */
        $refreshTokenEntity = $this->find($tokenId);

        if (null === $refreshTokenEntity) {
            return true;
        }

        return $refreshTokenEntity->getIsRevoked();
    }
}
