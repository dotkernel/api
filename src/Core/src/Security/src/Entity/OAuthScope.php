<?php

declare(strict_types=1);

namespace Core\Security\Entity;

use Core\Security\Repository\OAuthScopeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\ScopeTrait;

#[ORM\Entity(repositoryClass: OAuthScopeRepository::class)]
#[ORM\Table(name: 'oauth_scopes')]
class OAuthScope implements ScopeEntityInterface
{
    use ScopeTrait;

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'scope', type: 'string', length: 191)]
    private string $scope = '';

    /** @var Collection<int, AccessTokenEntityInterface> */
    #[ORM\ManyToMany(targetEntity: OAuthAccessToken::class, mappedBy: 'scopes')]
    protected Collection $accessTokens;

    /** @var Collection<int, AuthCodeEntityInterface> */
    #[ORM\ManyToMany(targetEntity: OAuthAuthCode::class, mappedBy: 'scopes')]
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

    public function addAccessToken(AccessTokenEntityInterface $accessToken): self
    {
        if (! $this->accessTokens->contains($accessToken)) {
            $this->accessTokens->add($accessToken);
        }

        return $this;
    }

    public function removeAccessToken(AccessTokenEntityInterface $accessToken): self
    {
        if ($this->accessTokens->contains($accessToken)) {
            $this->accessTokens->removeElement($accessToken);
        }

        return $this;
    }

    /**
     * @return Collection<int, AccessTokenEntityInterface>
     */
    public function getAccessTokens(?Criteria $criteria = null): Collection
    {
        if ($criteria === null) {
            return $this->accessTokens;
        }

        return $this->accessTokens->matching($criteria);
    }

    public function addAuthCode(AuthCodeEntityInterface $authCode): self
    {
        if (! $this->authCodes->contains($authCode)) {
            $this->authCodes->add($authCode);
        }

        return $this;
    }

    public function removeAuthCode(AuthCodeEntityInterface $authCode): self
    {
        if ($this->authCodes->contains($authCode)) {
            $this->authCodes->removeElement($authCode);
        }

        return $this;
    }

    /**
     * @return Collection<int, AuthCodeEntityInterface>
     */
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
