<?php

declare(strict_types=1);

namespace Core\Security\Entity;

use Core\App\Entity\NumericIdentifierTrait;
use Core\Security\Repository\OAuthAccessTokenRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use League\OAuth2\Server\CryptKeyInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use RuntimeException;

use function is_int;

#[ORM\Entity(repositoryClass: OAuthAccessTokenRepository::class)]
#[ORM\Table(name: 'oauth_access_tokens')]
class OAuthAccessToken implements AccessTokenEntityInterface
{
    use NumericIdentifierTrait;

    #[ORM\ManyToOne(targetEntity: OAuthClient::class)]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id')]
    private ClientEntityInterface $client;

    /** @var non-empty-string $userId */
    #[ORM\Column(name: 'user_id', type: 'string', length: 25)]
    private string $userId;

    /** @var non-empty-string $token */
    #[ORM\Column(name: 'token', type: 'string', length: 100)]
    private string $token;

    #[ORM\Column(name: 'revoked', type: 'boolean', options: ['default' => false])]
    private bool $isRevoked = false;

    /** @var Collection<int, ScopeEntityInterface> */
    #[ORM\ManyToMany(targetEntity: OAuthScope::class, inversedBy: 'accessTokens', indexBy: 'id')]
    #[ORM\JoinTable(name: 'oauth_access_token_scopes')]
    #[ORM\JoinColumn(name: 'access_token_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'scope_id', referencedColumnName: 'id')]
    protected Collection $scopes;

    #[ORM\Column(name: 'expires_at', type: 'datetime_immutable')]
    private DateTimeImmutable $expiresAt;

    private ?CryptKeyInterface $privateKey = null;

    private ?Configuration $jwtConfiguration = null;

    public function __construct()
    {
        $this->scopes = new ArrayCollection();
    }

    public function setClient(ClientEntityInterface $client): void
    {
        $this->client = $client;
    }

    public function getClient(): ClientEntityInterface
    {
        return $this->client;
    }

    /**
     * @return non-empty-string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param non-empty-string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function setIsRevoked(bool $isRevoked): self
    {
        $this->isRevoked = $isRevoked;

        return $this;
    }

    public function getIsRevoked(): bool
    {
        return $this->isRevoked;
    }

    public function revoke(): self
    {
        $this->isRevoked = true;

        return $this;
    }

    /**
     * @return non-empty-string
     */
    public function getIdentifier(): string
    {
        return $this->getToken();
    }

    /**
     * @param mixed $identifier
     */
    public function setIdentifier($identifier): void
    {
        $this->setToken($identifier);
    }

    /**
     * @param non-empty-string|int $identifier
     */
    public function setUserIdentifier($identifier): void
    {
        if (is_int($identifier)) {
            $identifier = (string) $identifier;
        }

        $this->userId = $identifier;
    }

    public function getUserIdentifier(): string
    {
        return $this->userId;
    }

    public function addScope(ScopeEntityInterface $scope): void
    {
        if (! $this->scopes->contains($scope)) {
            $this->scopes->add($scope);
        }
    }

    public function removeScope(OAuthScope $scope): self
    {
        if ($this->scopes->contains($scope)) {
            $this->scopes->removeElement($scope);
        }

        return $this;
    }

    public function getScopes(?Criteria $criteria = null): array
    {
        if ($criteria === null) {
            return $this->scopes->toArray();
        }

        return $this->scopes->matching($criteria)->toArray();
    }

    public function getExpiryDateTime(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiryDateTime(DateTimeImmutable $dateTime): void
    {
        $this->expiresAt = $dateTime;
    }

    public function setPrivateKey(CryptKeyInterface $privateKey): void
    {
        $this->privateKey = $privateKey;
    }

    public function initJwtConfiguration(): self
    {
        if (null === $this->privateKey) {
            throw new RuntimeException('Unable to init JWT without private key');
        }

        /** @var non-empty-string $keyContents */
        $keyContents = $this->privateKey->getKeyContents();
        $passphrase  = (string) $this->privateKey->getPassPhrase();

        $this->jwtConfiguration = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($keyContents, $passphrase),
            InMemory::plainText('/')
        );

        return $this;
    }

    private function convertToJWT(): Token
    {
        $this->initJwtConfiguration();

        if ($this->jwtConfiguration === null) {
            throw new RuntimeException('Unable to convert to JWT without config');
        }

        /** @var non-empty-string $audiences */
        $audiences = $this->getClient()->getIdentifier();
        /** @var non-empty-string $subject */
        $subject = (string) $this->getUserIdentifier();
        return $this->jwtConfiguration->builder()
            ->permittedFor($audiences)
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo($subject)
            ->withClaim('scopes', $this->getScopes())
            ->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());
    }

    public function __toString(): string
    {
        return $this->convertToJWT()->toString();
    }

    public function toString(): string
    {
        return $this->convertToJWT()->toString();
    }
}
