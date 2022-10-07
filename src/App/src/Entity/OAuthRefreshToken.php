<?php


namespace Api\App\Entity;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

/**
 * Class OAuthRefreshToken
 * @ORM\Entity(repositoryClass="Api\App\Repository\OAuthRefreshTokenRepository")
 * @ORM\Table(name="oauth_refresh_tokens")
 * @package Api\App\Entity
 */
class OAuthRefreshToken implements RefreshTokenEntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Api\App\Entity\OAuthAccessToken")
     * @ORM\JoinColumn(name="access_token_id", referencedColumnName="id")
     */
    private AccessTokenEntityInterface $accessToken;

    /**
     * @ORM\Column(name="revoked", type="boolean", options={"default":0})
     */
    private bool $isRevoked = false;

    /**
     * @ORM\Column(name="expires_at", type="datetime_immutable")
     */
    private DateTimeImmutable $expiresAt;

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
        return (string)$this->getId();
    }

    public function setIdentifier($identifier): self
    {
        $this->setId((int)$identifier);

        return $this;
    }

    public function setAccessToken(AccessTokenEntityInterface $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getAccessToken(): AccessTokenEntityInterface
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

    public function getExpiryDateTime(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiryDateTime(DateTimeImmutable $dateTime): self
    {
        $this->expiresAt = $dateTime;

        return $this;
    }
}
