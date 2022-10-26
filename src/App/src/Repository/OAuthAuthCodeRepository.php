<?php

namespace Api\App\Repository;

use Api\App\Entity\OAuthAuthCode;
use Doctrine\ORM\EntityRepository;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use Doctrine\ORM as ORM;

/**
 * Class OAuthAuthCodeRepository
 * @package Api\App\Repository
 */
class OAuthAuthCodeRepository extends EntityRepository implements AuthCodeRepositoryInterface
{
    public function getNewAuthCode(): OAuthAuthCode
    {
        return new OAuthAuthCode();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $this->getEntityManager()->persist($authCodeEntity);
        $this->getEntityManager()->flush();
    }

    public function revokeAuthCode($codeId)
    {
        /** @var ?OAuthAuthCode $authCodeEntity */
        $authCodeEntity = $this->find($codeId);
        if (null === $authCodeEntity) {
            return;
        }
        $authCodeEntity->setIsRevoked(true);
        $this->getEntityManager()->persist($authCodeEntity);
        $this->getEntityManager()->flush();
    }

    public function isAuthCodeRevoked($codeId): bool
    {
        /** @var ?OAuthAuthCode $authCodeEntity */
        $authCodeEntity = $this->find($codeId);
        if (null === $authCodeEntity) {
            return true;
        }

        return $authCodeEntity->getIsRevoked();
    }
}
