<?php

namespace Api\App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Lcobucci\JWT\Signer\Key\InMemory;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token;
use DateTimeImmutable;

/**
 * Class OAuthAccessToken
 * @ORM\Entity(repositoryClass="Api\App\Repository\OAuthAccessTokenRepository")
 * @ORM\Table(name="oauth_access_tokens")
 * @package Api\App\Entity
 */
class OAuthAccessToken implements AccessTokenEntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Api\App\Entity\OAuthClient")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    private ClientEntityInterface $client;

    /**
     * @ORM\Column(name="user_id", type="string", nullable=true)
     * @var string|null $userId
     */
    private ?string $userId;

    /**
     * @ORM\Column(name="token", type="string", length=100)
     * @var string $token
     */
    private string $token;

    /**
     * @ORM\Column(name="revoked", type="boolean", options={"default":0})
     * @var bool $isRevoked
     */
    private bool $isRevoked = false;

    /**
     * @ORM\ManyToMany(targetEntity="Api\App\Entity\OAuthScope", inversedBy="accessTokens", indexBy="id")
     * @ORM\JoinTable(name="oauth_access_token_scopes",
     *      joinColumns={@ORM\JoinColumn(name="access_token_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="scope_id", referencedColumnName="id")}
     *      )
     */
    protected Collection $scopes;

    /**
     * @ORM\Column(name="expires_at", type="datetime_immutable")
     */
    private DateTimeImmutable $expiresAt;

    private ?CryptKey $privateKey = null;

    private ?Configuration $jwtConfiguration = null;

    /**
     * OAuthAccessToken constructor.
     */
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

    public function getIdentifier(): string
    {
        return $this->getToken();
    }

    public function setIdentifier($identifier): self
    {
        return $this->setToken($identifier);
    }

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
        if ($this->scopes->contains($scope)) {
            return $this;
        }

        $this->scopes->add($scope);

        return $this;
    }

    public function removeScope(OAuthScope $scope): self
    {
        $this->scopes->removeElement($scope);

        return $this;
    }

    public function getScopes(?Criteria $criteria = null): array
    {
        if ($criteria === null) {
            return $this->scopes->toArray();
        }

        /** @psalm-suppress UndefinedInterfaceMethod */
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
            throw new \RuntimeException('Unable to init JWT without private key');
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

    /**
     * Generate a JWT from the access token
     */
    private function convertToJWT(): Token
    {
        $this->initJwtConfiguration();

        if (null === $this->jwtConfiguration) {
            throw new \RuntimeException('Unable to convert to JWT without config');
        }

        return $this->jwtConfiguration->builder()
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new \DateTimeImmutable())
            ->canOnlyBeUsedAfter(new \DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo($this->getUserIdentifier())
            ->withClaim('scopes', $this->getScopes())
            ->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());
    }

    /**
     * Generate a string representation from the access token
     */
    public function __toString(): string
    {
        return $this->convertToJWT()->toString();
    }
}
