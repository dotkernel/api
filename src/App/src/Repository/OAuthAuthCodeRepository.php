<?php

declare(strict_types=1);

namespace Api\App\Repository;

use Api\App\Entity\OAuthAuthCode;
use Doctrine\ORM\EntityRepository;
use Dot\DependencyInjection\Attribute\Entity;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

/**
 * @extends EntityRepository<object>
 */
#[Entity(name: OAuthAuthCode::class)]
class OAuthAuthCodeRepository extends EntityRepository implements AuthCodeRepositoryInterface
{
    public function getNewAuthCode(): OAuthAuthCode
    {
        return new OAuthAuthCode();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        $this->getEntityManager()->persist($authCodeEntity);
        $this->getEntityManager()->flush();
    }

    /**
     * @param string $codeId
     */
    public function revokeAuthCode($codeId): void
    {
        $authCodeEntity = $this->find($codeId);
        if ($authCodeEntity instanceof OAuthAuthCode) {
            $this->getEntityManager()->persist($authCodeEntity->revoke());
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param string $codeId
     */
    public function isAuthCodeRevoked($codeId): bool
    {
        $authCodeEntity = $this->find($codeId);
        if ($authCodeEntity instanceof OAuthAuthCode) {
            return $authCodeEntity->getIsRevoked();
        }

        return true;
    }
}
