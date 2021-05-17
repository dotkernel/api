<?php

declare(strict_types=1);

namespace Api\App\Repository;

use Mezzio\Authentication\OAuth2\Repository\Pdo\AccessTokenRepository as AccessTokenRepositoryAlias;

/**
 * Class AccessTokenRepository
 * @package Api\App\Repository
 */
class AccessTokenRepository extends AccessTokenRepositoryAlias
{
    /**
     * {@inheritDoc}
     */
    public function isAccessTokenRevoked($tokenId): bool
    {
        $sth = $this->pdo->prepare(
            'SELECT revoked FROM oauth_access_tokens WHERE id = :tokenId'
        );
        $sth->bindParam(':tokenId', $tokenId);

        if (false === $sth->execute()) {
            return false;
        }
        $row = $sth->fetch();
        if (empty($row)) {
            return true;
        }

        return array_key_exists('revoked', $row) && $row['revoked'];
    }
}
