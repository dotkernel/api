<?php

declare(strict_types=1);

namespace Core\Security\Repository;

use Core\App\Repository\AbstractRepository;
use Core\Security\Entity\OAuthClient;
use Dot\DependencyInjection\Attribute\Entity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

use function in_array;
use function password_verify;

#[Entity(name: OAuthClient::class)]
class OAuthClientRepository extends AbstractRepository implements ClientRepositoryInterface
{
    private const string GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';
    private const string GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';
    private const string GRANT_TYPE_REFRESH_TOKEN      = 'refresh_token';
    private const string GRANT_TYPE_PASSWORD           = 'password';

    private const array GRANT_TYPES = [
        self::GRANT_TYPE_CLIENT_CREDENTIALS,
        self::GRANT_TYPE_AUTHORIZATION_CODE,
        self::GRANT_TYPE_REFRESH_TOKEN,
        self::GRANT_TYPE_PASSWORD,
    ];

    public function getClientEntity(string $clientIdentifier): ?ClientEntityInterface
    {
        $client = $this->findOneBy(['name' => $clientIdentifier]);
        if ($client instanceof OAuthClient) {
            return $client;
        }

        return null;
    }

    public function validateClient(string $clientIdentifier, ?string $clientSecret, ?string $grantType): bool
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
