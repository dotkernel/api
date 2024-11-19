<?php

declare(strict_types=1);

namespace Api\App\Repository;

use Api\App\Entity\OAuthClient;
use Doctrine\ORM\EntityRepository;
use Dot\DependencyInjection\Attribute\Entity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

use function in_array;
use function password_verify;

/**
 * @extends EntityRepository<object>
 */
#[Entity(name: OAuthClient::class)]
class OAuthClientRepository extends EntityRepository implements ClientRepositoryInterface
{
    private const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';
    private const GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';
    private const GRANT_TYPE_REFRESH_TOKEN      = 'refresh_token';
    private const GRANT_TYPE_PASSWORD           = 'password';

    private const GRANT_TYPES = [
        self::GRANT_TYPE_CLIENT_CREDENTIALS,
        self::GRANT_TYPE_AUTHORIZATION_CODE,
        self::GRANT_TYPE_REFRESH_TOKEN,
        self::GRANT_TYPE_PASSWORD,
    ];

    /**
     * @param string $clientIdentifier
     */
    public function getClientEntity($clientIdentifier): ?ClientEntityInterface
    {
        $client = $this->findOneBy(['name' => $clientIdentifier]);
        if ($client instanceof OAuthClient) {
            return $client;
        }

        return null;
    }

    /**
     * @param string $clientIdentifier
     * @param null|string $clientSecret
     * @param null|string $grantType
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        $client = $this->getClientEntity($clientIdentifier);
        if (! $client instanceof OAuthClient) {
            return false;
        }

        if (! $this->isGranted($grantType)) {
            return false;
        }

        if (empty($client->getSecret())) {
            return false;
        }

        return password_verify((string) $clientSecret, $client->getSecret());
    }

    private function isGranted(?string $grantType = null): bool
    {
        return in_array($grantType, self::GRANT_TYPES);
    }
}
