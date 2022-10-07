<?php

declare(strict_types=1);

namespace Api\App\Entity;

use Api\User\Entity\User;
use Mezzio\Authentication\UserInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class OauthClient
 * @ORM\Entity(repositoryClass="Api\App\Repository\OAuthClientRepository")
 * @ORM\Table(name="oauth_clients")
 * @package Api\App\Entity
 */
class OAuthClient implements ClientEntityInterface
{
    public const NAME_ADMIN = 'admin';
    public const NAME_FRONTEND = 'frontend';

    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Api\User\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="uuid", nullable=true)
     */
    private ?User $user = null;

    /**
     * @ORM\Column(name="name", type="string", length=40)
     */
    private string $name;

    /**
     * @ORM\Column(name="secret", type="string", length=100, nullable=true)
     */
    private ?string $secret;

    /**
     * @ORM\Column(name="redirect", type="string", length=191)
     */
    private string $redirect;

    /**
     * @ORM\Column(name="revoked", type="boolean", options={"default":0})
     */
    private bool $isRevoked = false;

    /**
     * @ORM\Column(name="isConfidential", type="boolean", options={"default":0})
     */
    private bool $isConfidential = false;

    private array $roles = [];

    public function __construct()
    {
        $this->id = 0;
        $this->name = '';
        $this->secret = null;
        $this->redirect = '';
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

    public function setUser(?User $user = null): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getIdentity(): string
    {
        return $this->getName();
    }

    public function getIdentifier(): string
    {
        return $this->getName();
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setSecret(?string $secret = null): self
    {
        $this->secret = $secret;

        return $this;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setRedirect(string $redirect): self
    {
        $this->redirect = $redirect;

        return $this;
    }

    public function getRedirect(): string
    {
        return $this->redirect;
    }

    public function getRedirectUri(): ?string
    {
        return $this->getRedirect();
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

    public function setIsConfidential(bool $isConfidential): self
    {
        $this->isConfidential = $isConfidential;

        return $this;
    }

    public function getIsConfidential(): bool
    {
        return $this->isConfidential;
    }

    public function isConfidential(): bool
    {
        return $this->getIsConfidential();
    }
}
