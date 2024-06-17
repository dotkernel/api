<?php

declare(strict_types=1);

namespace Api\App\Entity;

use Api\App\Repository\OAuthAccessTokenRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use RuntimeException;

#[ORM\Entity(repositoryClass: OAuthAccessTokenRepository::class)]
#[ORM\Table(name: "oauth_access_tokens")]
class OAuthAccessToken implements AccessTokenEntityInterface
{
    #[ORM\Id]
    #[ORM\Column(name: "id", type: "integer", options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: OAuthClient::class)]
    #[ORM\JoinColumn(name: "client_id", referencedColumnName: "id")]
    private ClientEntityInterface $client;

    #[ORM\Column(name: "user_id", type: "string", length: 25, nullable: true)]
    private ?string $userId;

    #[ORM\Column(name: "token", type: "string", length: 100)]
    private string $token;

    #[ORM\Column(name: "revoked", type: "boolean", options: ['default' => false])]
    private bool $isRevoked = false;

    #[ORM\ManyToMany(targetEntity: OAuthScope::class, inversedBy: "accessTokens", indexBy: "id")]
    #[ORM\JoinTable(name: "oauth_access_token_scopes")]
    #[ORM\JoinColumn(name: "access_token_id", referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: "scope_id", referencedColumnName: "id")]
    protected Collection $scopes;

    #[ORM\Column(name: 'expires_at', type: "datetime_immutable")]
    private DateTimeImmutable $expiresAt;

    private ?CryptKey $privateKey = null;

    private ?Configuration $jwtConfiguration = null;

    public function __construct()
    {
        $this->scopes = new ArrayCollection();
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setClient(ClientEntityInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getClient(): ClientEntityInterface
    {
        return $this->client;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
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

    public function getIdentifier(): string
    {
        return $this->getToken();
    }

    /**
     * @param mixed $identifier
     */
    public function setIdentifier($identifier): self
    {
        return $this->setToken($identifier);
    }

    /**
     * @param string|int|null $identifier
     */
    public function setUserIdentifier($identifier): self
    {
        $this->userId = $identifier;

        return $this;
    }

    public function getUserIdentifier(): ?string
    {
        return $this->userId;
    }

    public function addScope(ScopeEntityInterface $scope): self
    {
        if (! $this->scopes->contains($scope)) {
            $this->scopes->add($scope);
        }

        return $this;
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

    public function setExpiryDateTime(DateTimeImmutable $dateTime): self
    {
        $this->expiresAt = $dateTime;

        return $this;
    }

    public function setPrivateKey(CryptKey $privateKey): self
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    public function initJwtConfiguration(): self
    {
        if (null === $this->privateKey) {
            throw new RuntimeException('Unable to init JWT without private key');
        }

        $this->jwtConfiguration = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText(
                $this->privateKey->getKeyContents(),
                $this->privateKey->getPassPhrase() ?? ''
            ),
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

        return $this->jwtConfiguration->builder()
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo($this->getUserIdentifier())
            ->withClaim('scopes', $this->getScopes())
            ->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());
    }

    public function __toString(): string
    {
        return $this->convertToJWT()->toString();
    }
}
