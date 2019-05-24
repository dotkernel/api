<?php

declare(strict_types=1);

namespace App\Common;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Zend\Expressive\Authentication\OAuth2\Repository\Pdo\AbstractRepository;
use Zend\Expressive\Authentication\OAuth2\Entity\UserEntity;

/**
 * Class OauthUserRepository
 * @package App\Common
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
            'SELECT password FROM `user` WHERE username = :username'
        );
        $sth->bindParam(':username', $username);

        if (false === $sth->execute()) {
            return;
        }

        $row = $sth->fetch();

        if (! empty($row) && password_verify($password, $row['password'])) {
            return new UserEntity($username);
        }

        return;
    }
}
