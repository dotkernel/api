<?php

declare(strict_types=1);

namespace Api\App\Common\Repository;

use Api\App\Common\Message;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\AbstractRepository;
use Zend\Expressive\Authentication\OAuth2\Entity\UserEntity;

use function password_verify;

/**
 * Class OauthUserRepository
 * @package Api\App\Common
 */
class OauthUserRepository extends AbstractRepository implements UserRepositoryInterface
{
    /**
     * @param string $username
     * @param string $password
     * @param string $grantType
     * @param ClientEntityInterface $clientEntity
     * @return UserEntityInterface|void
     * @throws OAuthServerException
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        $sth = $this->pdo->prepare('SELECT password, status FROM user WHERE username = :username AND isDeleted = 0');
        $sth->bindParam(':username', $username);

        if (false === $sth->execute()) {
            return;
        }

        $row = $sth->fetch();
        if (empty($row)) {
            return;
        }

        if (!password_verify($password, $row['password'])) {
            return;
        }

        if ($row['status'] !== \Api\User\Entity\UserEntity::STATUS_ACTIVE) {
            throw new OAuthServerException(Message::USER_NOT_ACTIVATED, 6, 'inactive_user', 401);
        }

        return new UserEntity($username);
    }
}
