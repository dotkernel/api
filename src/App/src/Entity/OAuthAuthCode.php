<?php

declare(strict_types=1);

namespace Api\App\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;

/**
 * @ORM\Entity(repositoryClass="Api\App\Repository\OAuthAuthCodeRepository")
 * @ORM\Table(name="oauth_auth_codes")
 */
class OAuthAuthCode implements AuthCodeEntityInterface
{
    use AuthCodeTrait;

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

    /** @ORM\Column(name="revoked", type="boolean", options={"default":0}) */
    private bool $isRevoked = false;

    /**
     * @ORM\ManyToMany(targetEntity="Api\App\Entity\OAuthSCope", inversedBy="authCodes", indexBy="id")
     * @ORM\JoinTable(name="oauth_auth_code_scopes",
     *     joinColumns={@ORM\JoinColumn(name="auth_code_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="scope_id", referencedColumnName="id")}
     * )
     */
    protected Collection $scopes;

    /** @ORM\Column(type="datetime_immutable", nullable=true) */
    private DateTimeImmutable $expiresDatetime;

    public function __construct()
    {
        $this->expiresDatetime = new DateTimeImmutable();
        $this->scopes          = new ArrayCollection();
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

    public function getIdentifier(): ?int
    {
        return $this->getId();
    }

    /**
     * @param mixed $identifier
     */
    public function setIdentifier($identifier): self
    {
        $this->setId($identifier);

        return $this;
    }

    /**
     * @param string|int|null $identifier
     */
    public function setUserIdentifier($identifier): self
    {
        return $this;
    }

    public function getUserIdentifier(): ?string
    {
        /** @var OAuthClient $client */
        $client = $this->getClient();

        if (null === $user = $client->getUser()) {
            return null;
        }

        return $user->getIdentifier();
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

    public function addScope(ScopeEntityInterface $scope): self
    {
        if (! $this->scopes->contains($scope)) {
            $this->scopes->add($scope);
        }

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

        return $this->scopes->matching($criteria)->toArray();
    }

    public function setExpiresDatetime(DateTimeImmutable $expiresDatetime): self
    {
        $this->expiresDatetime = $expiresDatetime;

        return $this;
    }

    public function getExpiresDatetime(): DateTimeImmutable
    {
        return $this->expiresDatetime;
    }

    public function getExpiryDateTime(): DateTimeImmutable
    {
        return $this->getExpiresDatetime();
    }

    public function setExpiryDateTime(DateTimeImmutable $dateTime): self
    {
        return $this->setExpiresDatetime($dateTime);
    }
}
