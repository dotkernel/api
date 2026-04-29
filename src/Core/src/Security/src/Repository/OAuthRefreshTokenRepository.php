<?php

declare(strict_types=1);

namespace Core\Security\Repository;

use Core\App\Repository\AbstractRepository;
use Core\Security\Entity\OAuthRefreshToken;
use Dot\DependencyInjection\Attribute\Entity;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

#[Entity(name: OAuthRefreshToken::class)]
class OAuthRefreshTokenRepository extends AbstractRepository implements RefreshTokenRepositoryInterface
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

    public function revokeRefreshToken(string $tokenId): void
    {
        $refreshTokenEntity = $this->find($tokenId);
        if ($refreshTokenEntity instanceof OAuthRefreshToken) {
            $this->getEntityManager()->persist($refreshTokenEntity->revoke());
            $this->getEntityManager()->flush();
        }
    }

    public function isRefreshTokenRevoked(string $tokenId): bool
    {
        $refreshTokenEntity = $this->find($tokenId);
        if ($refreshTokenEntity instanceof OAuthRefreshToken) {
            return $refreshTokenEntity->getIsRevoked();
        }

        return true;
    }
}
