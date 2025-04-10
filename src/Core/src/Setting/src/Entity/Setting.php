<?php

declare(strict_types=1);

namespace Core\Setting\Entity;

use Core\Admin\Entity\Admin;
use Core\App\Entity\AbstractEntity;
use Core\App\Entity\TimestampsTrait;
use Core\Setting\Enum\SettingIdentifierEnum;
use Core\Setting\Repository\SettingRepository;
use Doctrine\ORM\Mapping as ORM;

use function array_unique;
use function json_decode;
use function json_encode;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
#[ORM\Table(name: 'settings')]
#[ORM\HasLifecycleCallbacks]
class Setting extends AbstractEntity
{
    use TimestampsTrait;

    #[ORM\ManyToOne(targetEntity: Admin::class, inversedBy: 'settings')]
    #[ORM\JoinColumn(name: 'admin_uuid', referencedColumnName: 'uuid')]
    protected ?Admin $admin = null;

    #[ORM\Column(type: 'setting_enum', enumType: SettingIdentifierEnum::class)]
    protected ?SettingIdentifierEnum $identifier = null;

    #[ORM\Column(name: 'value', type: 'text')]
    protected ?string $value = null;

    public function __construct(Admin $admin, SettingIdentifierEnum $identifier, array $value)
    {
        parent::__construct();

        $this->setAdmin($admin);
        $this->setIdentifier($identifier);
        $this->setValue($value);
    }

    public function getAdmin(): ?Admin
    {
        return $this->admin;
    }

    public function setAdmin(Admin $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getIdentifier(): ?SettingIdentifierEnum
    {
        return $this->identifier;
    }

    public function setIdentifier(SettingIdentifierEnum $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getValue(): mixed
    {
        return json_decode($this->value, true);
    }

    public function setValue(array $value): self
    {
        $this->value = json_encode(array_unique($value));

        return $this;
    }

    public function getArrayCopy(): array
    {
        return [
            'identifier' => $this->identifier->value,
            'value'      => $this->getValue(),
        ];
    }
}
