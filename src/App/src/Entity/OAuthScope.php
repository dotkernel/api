<?php

declare(strict_types=1);

namespace Api\App\Entity;

use Api\App\Repository\OAuthScopeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\ScopeTrait;

#[ORM\Entity(repositoryClass: OAuthScopeRepository::class)]
#[ORM\Table(name: "oauth_scopes")]
class OAuthScope implements ScopeEntityInterface
{
    use ScopeTrait;

    #[ORM\Id]
    #[ORM\Column(name: "id", type: "integer", options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private int $id;

    #[ORM\Column(name: "scope", type: "string", length: 191)]
    private string $scope = '';

    #[ORM\ManyToMany(targetEntity: OAuthAccessToken::class, mappedBy: "scopes")]
    protected Collection $accessTokens;

    #[ORM\ManyToMany(targetEntity: OAuthAuthCode::class, mappedBy: "scopes")]
    protected Collection $authCodes;

    public function __construct()
    {
        $this->accessTokens = new ArrayCollection();
        $this->authCodes    = new ArrayCollection();
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

    public function getIdentifier(): string
    {
        return $this->getScope();
    }

    public function setScope(string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function addAccessToken(OAuthAccessToken $accessToken): self
    {
        if (! $this->accessTokens->contains($accessToken)) {
            $this->accessTokens->add($accessToken);
        }

        return $this;
    }

    public function removeAccessToken(OAuthAccessToken $accessToken): self
    {
        if ($this->accessTokens->contains($accessToken)) {
            $this->accessTokens->removeElement($accessToken);
        }

        return $this;
    }

    public function getAccessTokens(?Criteria $criteria = null): Collection
    {
        if ($criteria === null) {
            return $this->accessTokens;
        }

        return $this->accessTokens->matching($criteria);
    }

    public function addAuthCode(OAuthAuthCode $authCode): self
    {
        if (! $this->authCodes->contains($authCode)) {
            $this->authCodes->add($authCode);
        }

        return $this;
    }

    public function removeAuthCode(OAuthAuthCode $authCode): self
    {
        if ($this->authCodes->contains($authCode)) {
            $this->authCodes->removeElement($authCode);
        }

        return $this;
    }

    public function getAuthCodes(?Criteria $criteria = null): Collection
    {
        if ($criteria === null) {
            return $this->authCodes;
        }

        return $this->authCodes->matching($criteria);
    }

    public function jsonSerialize(): string
    {
        return $this->getIdentifier();
    }
}
