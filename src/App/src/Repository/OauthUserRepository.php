<?php

declare(strict_types=1);

namespace Api\App\Repository;

use Api\App\Message;
use Api\User\Entity\User as User;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Mezzio\Authentication\OAuth2\Repository\Pdo\AbstractRepository;
use Mezzio\Authentication\OAuth2\Entity\UserEntity;

use function password_verify;

/**
 * Class OauthUserRepository
 * @package Api\App\Repository
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
        switch ($clientEntity->getName()) {
            case 'admin':
                $sth = $this->pdo->prepare('SELECT password FROM admin WHERE identity = :identity');
                break;
            case 'frontend':
                $sth = $this->pdo->prepare(
                    'SELECT password, status FROM user WHERE identity = :identity AND isDeleted = 0'
                );
                break;
            default:
                throw new OAuthServerException(Message::INVALID_CLIENT_ID, 6, 'invalid_client', 401);
        }
        $sth->bindParam(':identity', $username);

        if (! $sth->execute()) {
            throw new OAuthServerException($sth->errorInfo()[2], $sth->errorInfo()[1], 'general_error', 500);
        }

        $row = $sth->fetch();
        if (empty($row)) {
            return;
        }

        if (!password_verify($password, $row['password'])) {
            return;
        }

        if ($clientEntity->getName() == 'frontend' && $row['status'] !== User::STATUS_ACTIVE) {
            throw new OAuthServerException(Message::USER_NOT_ACTIVATED, 6, 'inactive_user', 401);
        }

        return new UserEntity($username);
    }
}
