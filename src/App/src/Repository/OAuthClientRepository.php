<?php

namespace Api\App\Repository;

use Doctrine\ORM\EntityRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

/**
 * Class OAuthClientRepository
 * @package Api\App\Repository
 *
 * @psalm-suppress UndefinedInterfaceMethod
 */
class OAuthClientRepository extends EntityRepository implements ClientRepositoryInterface
{
    private const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';
    private const GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';
    private const GRANT_TYPE_REFRESH_TOKEN = 'refresh_token';
    private const GRANT_TYPE_PASSWORD = 'password';

    private const GRANT_TYPES = [
        self::GRANT_TYPE_CLIENT_CREDENTIALS,
        self::GRANT_TYPE_AUTHORIZATION_CODE,
        self::GRANT_TYPE_REFRESH_TOKEN,
        self::GRANT_TYPE_PASSWORD
    ];

    public function getClientEntity($clientIdentifier): ?ClientEntityInterface
    {
        /** @var ClientEntityInterface|null $client */
        $client = $this->findOneBy(['name' => $clientIdentifier]);

        return $client;
    }

    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        $client = $this->getClientEntity($clientIdentifier);
        if (null === $client) {
            return false;
        }

        if (! $this->isGranted($client, $grantType)) {
            return false;
        }

        if (empty($client->getSecret())) {
            return false;
        }

        return password_verify((string)$clientSecret, $client->getSecret());
    }

    private function isGranted(ClientEntityInterface $client, string $grantType = null): bool
    {
        return in_array($grantType, self::GRANT_TYPES);
    }
}
