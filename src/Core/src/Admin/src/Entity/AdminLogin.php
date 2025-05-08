<?php

declare(strict_types=1);

namespace Core\Admin\Entity;

use Core\Admin\Repository\AdminLoginRepository;
use Core\App\Entity\AbstractEntity;
use Core\App\Entity\TimestampsTrait;
use Core\App\Enum\SuccessFailureEnum;
use Core\App\Enum\YesNoEnum;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminLoginRepository::class)]
#[ORM\Table(name: 'admin_login')]
#[ORM\HasLifecycleCallbacks]
class AdminLogin extends AbstractEntity
{
    use TimestampsTrait;

    #[ORM\Column(name: 'identity', type: 'string', length: 191, nullable: true)]
    protected ?string $identity = null;

    #[ORM\Column(name: 'adminIp', type: 'string', length: 191, nullable: true)]
    protected ?string $adminIp = null;

    #[ORM\Column(name: 'country', type: 'string', length: 191, nullable: true)]
    protected ?string $country = null;

    #[ORM\Column(name: 'continent', type: 'string', length: 191, nullable: true)]
    protected ?string $continent = null;

    #[ORM\Column(name: 'organization', type: 'string', length: 191, nullable: true)]
    protected ?string $organization = null;

    #[ORM\Column(name: 'deviceType', type: 'string', length: 191, nullable: true)]
    protected ?string $deviceType = null;

    #[ORM\Column(name: 'deviceBrand', type: 'string', length: 191, nullable: true)]
    protected ?string $deviceBrand = null;

    #[ORM\Column(name: 'deviceModel', type: 'string', length: 40, nullable: true)]
    protected ?string $deviceModel = null;

    #[ORM\Column(type: 'yes_no_enum', nullable: true, enumType: YesNoEnum::class)]
    protected YesNoEnum $isMobile = YesNoEnum::No;

    #[ORM\Column(name: 'osName', type: 'string', length: 191, nullable: true)]
    protected ?string $osName = null;

    #[ORM\Column(name: 'osVersion', type: 'string', length: 191, nullable: true)]
    protected ?string $osVersion = null;

    #[ORM\Column(name: 'osPlatform', type: 'string', length: 191, nullable: true)]
    protected ?string $osPlatform = null;

    #[ORM\Column(name: 'clientType', type: 'string', length: 191, nullable: true)]
    protected ?string $clientType = null;

    #[ORM\Column(name: 'clientName', type: 'string', length: 191, nullable: true)]
    protected ?string $clientName = null;

    #[ORM\Column(name: 'clientEngine', type: 'string', length: 191, nullable: true)]
    protected ?string $clientEngine = null;

    #[ORM\Column(name: 'clientVersion', type: 'string', length: 191, nullable: true)]
    protected ?string $clientVersion = null;

    #[ORM\Column(type: 'success_failure_enum', nullable: true, enumType: SuccessFailureEnum::class)]
    protected SuccessFailureEnum $loginStatus = SuccessFailureEnum::Fail;

    public function __construct()
    {
        parent::__construct();

        $this->created();
    }

    public function getIdentity(): ?string
    {
        return $this->identity;
    }

    public function setIdentity(string $identity): self
    {
        $this->identity = $identity;

        return $this;
    }

    public function getAdminIp(): ?string
    {
        return $this->adminIp;
    }

    public function setAdminIp(?string $adminIp): self
    {
        $this->adminIp = $adminIp;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getContinent(): ?string
    {
        return $this->continent;
    }

    public function setContinent(?string $continent): self
    {
        $this->continent = $continent;

        return $this;
    }

    public function getOrganization(): ?string
    {
        return $this->organization;
    }

    public function setOrganization(?string $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getDeviceType(): ?string
    {
        return $this->deviceType;
    }

    public function setDeviceType(?string $deviceType): self
    {
        $this->deviceType = $deviceType;

        return $this;
    }

    public function getDeviceBrand(): ?string
    {
        return $this->deviceBrand;
    }

    public function setDeviceBrand(?string $deviceBrand): self
    {
        $this->deviceBrand = $deviceBrand;

        return $this;
    }

    public function getDeviceModel(): ?string
    {
        return $this->deviceModel;
    }

    public function setDeviceModel(?string $deviceModel): self
    {
        $this->deviceModel = $deviceModel;

        return $this;
    }

    public function getIsMobile(): ?YesNoEnum
    {
        return $this->isMobile;
    }

    public function setIsMobile(YesNoEnum $isMobile): self
    {
        $this->isMobile = $isMobile;

        return $this;
    }

    public function getOsName(): ?string
    {
        return $this->osName;
    }

    public function setOsName(?string $osName): self
    {
        $this->osName = $osName;

        return $this;
    }

    public function getOsVersion(): ?string
    {
        return $this->osVersion;
    }

    public function setOsVersion(?string $osVersion): self
    {
        $this->osVersion = $osVersion;

        return $this;
    }

    public function getOsPlatform(): ?string
    {
        return $this->osPlatform;
    }

    public function setOsPlatform(?string $osPlatform): self
    {
        $this->osPlatform = $osPlatform;

        return $this;
    }

    public function getClientType(): ?string
    {
        return $this->clientType;
    }

    public function setClientType(?string $clientType): self
    {
        $this->clientType = $clientType;

        return $this;
    }

    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    public function setClientName(?string $clientName): self
    {
        $this->clientName = $clientName;

        return $this;
    }

    public function getClientEngine(): ?string
    {
        return $this->clientEngine;
    }

    public function setClientEngine(?string $clientEngine): self
    {
        $this->clientEngine = $clientEngine;

        return $this;
    }

    public function getClientVersion(): ?string
    {
        return $this->clientVersion;
    }

    public function setClientVersion(?string $clientVersion): self
    {
        $this->clientVersion = $clientVersion;

        return $this;
    }

    public function getLoginStatus(): ?SuccessFailureEnum
    {
        return $this->loginStatus;
    }

    public function setLoginStatus(SuccessFailureEnum $loginStatus): self
    {
        $this->loginStatus = $loginStatus;

        return $this;
    }

    /**
     * @return array{
     *     uuid: non-empty-string,
     *     identity: string|null,
     *     adminIp: string|null,
     *     country: string|null,
     *     continent: string|null,
     *     organization: string|null,
     *     deviceType: string|null,
     *     deviceBrand: string|null,
     *     deviceModel: string|null,
     *     isMobile: string,
     *     osName: string|null,
     *     osVersion: string|null,
     *     osPlatform: string|null,
     *     clientType: string|null,
     *     clientName: string|null,
     *     clientEngine: string|null,
     *     clientVersion: string|null,
     *     loginStatus: string,
     *     created: DateTimeImmutable,
     *     updated: DateTimeImmutable|null,
     * }
     */
    public function getArrayCopy(): array
    {
        return [
            'uuid'          => $this->uuid->toString(),
            'identity'      => $this->identity,
            'adminIp'       => $this->adminIp,
            'country'       => $this->country,
            'continent'     => $this->continent,
            'organization'  => $this->organization,
            'deviceType'    => $this->deviceType,
            'deviceBrand'   => $this->deviceBrand,
            'deviceModel'   => $this->deviceModel,
            'isMobile'      => $this->isMobile->value,
            'osName'        => $this->osName,
            'osVersion'     => $this->osVersion,
            'osPlatform'    => $this->osPlatform,
            'clientType'    => $this->clientType,
            'clientName'    => $this->clientName,
            'clientEngine'  => $this->clientEngine,
            'clientVersion' => $this->clientVersion,
            'loginStatus'   => $this->loginStatus->value,
            'created'       => $this->created,
            'updated'       => $this->updated,
        ];
    }
}
