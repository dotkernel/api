<?php

declare(strict_types=1);

namespace Api\App\Common\Repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\AbstractRepository;
use Zend\Expressive\Authentication\OAuth2\Entity\UserEntity;
use Zend\Http\Response;

use function header;
use function http_response_code;
use function json_encode;
use function password_verify;

/**
 * Class OauthUserRepository
 * @package Api\App\Common
 */
class OauthUserRepository extends AbstractRepository implements UserRepositoryInterface
{
    /**
     * Get a user entity.
     *
     * @param string $username
     * @param string $password
     * @param string $grantType The grant type used
     * @param ClientEntityInterface $clientEntity
     *
     * @return UserEntityInterface
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        $sth = $this->pdo->prepare(
            'SELECT password, status FROM user WHERE username = :username AND isDeleted = 0'
        );
        $sth->bindParam(':username', $username);

        if (false === $sth->execute()) {
            return;
        }

        $row = $sth->fetch();

        if (! empty($row) && password_verify($password, $row['password'])) {
            if ($row['status'] !== \Api\User\Entity\UserEntity::STATUS_ACTIVE) {
                http_response_code(Response::STATUS_CODE_400);
                header('Content-Type: application/json');
                exit(json_encode([
                    'error' => [
                        'messages' => [Message::USER_NOT_ACTIVATED]
                    ]
                ]));
            }

            return new UserEntity($username);
        }

        return;
    }
}
