<?php

declare(strict_types=1);

namespace Core\Security\Entity;

use Core\App\Entity\NumericIdentifierTrait;
use Core\Security\Repository\OAuthRefreshTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;

#[ORM\Entity(repositoryClass: OAuthRefreshTokenRepository::class)]
#[ORM\Table(name: 'oauth_refresh_tokens')]
class OAuthRefreshToken implements RefreshTokenEntityInterface
{
    use NumericIdentifierTrait;

    #[ORM\ManyToOne(targetEntity: OAuthAccessToken::class)]
    #[ORM\JoinColumn(name: 'access_token_id', referencedColumnName: 'id')]
    private AccessTokenEntityInterface $accessToken;

    #[ORM\Column(name: 'revoked', type: 'boolean', options: ['default' => false])]
    private bool $isRevoked = false;

    #[ORM\Column(name: 'expires_at', type: 'datetime_immutable')]
    private DateTimeImmutable $expiresAt;

    public function getIdentifier(): string
    {
        return (string) $this->getId();
    }

    public function setIdentifier(mixed $identifier): void
    {
    }

    public function setAccessToken(AccessTokenEntityInterface $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getAccessToken(): OAuthAccessToken|AccessTokenEntityInterface
    {
        return $this->accessToken;
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

    public function getExpiryDateTime(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiryDateTime(DateTimeImmutable $dateTime): void
    {
        $this->expiresAt = $dateTime;
    }
}
