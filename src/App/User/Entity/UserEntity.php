<?php

declare(strict_types=1);

namespace App\User\Entity;

use App\Common\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Stdlib\ArraySerializableInterface;

use function is_null;

/**
 * Class UserEntity
 * @ORM\Entity(repositoryClass="App\User\Repository\UserRepository")
 * @ORM\Table(name="user")
 * @package App\User\Entity
 */
class UserEntity extends AbstractEntity implements ArraySerializableInterface
{
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';

    /**
     * @ORM\Column(name="username", type="string", length=255)
     * @var string $email
     */
    protected $email;

    /**
     * @ORM\Column(name="password", type="string", length=255)
     * @var string $password
     */
    protected $password;

    /**
     * @ORM\Column(name="status", type="string", columnDefinition="ENUM('pending', 'active')"))
     * @var string $status
     */
    protected $status;

    /**
     * @ORM\OneToOne(targetEntity="UserAvatarEntity", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="avatarUuid", referencedColumnName="uuid", nullable=true, onDelete="CASCADE")
     * @var UserAvatarEntity $avatar
     */
    protected $avatar;

    /**
     * @ORM\OneToOne(targetEntity="UserDetailEntity", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="detailUuid", referencedColumnName="uuid", nullable=true, onDelete="CASCADE")
     * @var UserDetailEntity $detail
     */
    protected $detail;

    /**
     * @ORM\ManyToMany(targetEntity="UserRoleEntity", fetch="EAGER")
     * @ORM\JoinTable(
     *     name="user_roles",
     *     joinColumns={@ORM\JoinColumn(name="userUuid", referencedColumnName="uuid")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="roleUuId", referencedColumnName="uuid")}
     * )
     * @var $roles
     */
    protected $roles;

    /**
     * UserEntity constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->roles = new ArrayCollection();
        $this->status = self::STATUS_PENDING;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return UserAvatarEntity|null
     */
    public function getAvatar(): ?UserAvatarEntity
    {
        return $this->avatar;
    }

    /**
     * @param UserAvatarEntity $avatar
     * @return $this
     */
    public function setAvatar(UserAvatarEntity $avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return UserDetailEntity|null
     */
    public function getDetail(): ?UserDetailEntity
    {
        return $this->detail;
    }

    /**
     * @param UserDetailEntity $detail
     * @return $this
     */
    public function setDetail(UserDetailEntity $detail)
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles)
    {
        foreach ($roles as $role) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * Exchange internal values from provided array
     *
     * @param  array $array
     * @return void
     */
    public function exchangeArray(array $array)
    {
        $this
            ->setEmail($array['email'])
            ->setPassword($array['password'])
            ->setStatus($array['status'])
            ->setDetail($array['detaild'])
            ->setRoles($array['roles']);
    }

    /**
     * Return an array representation of the object
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return [
            'uuid' => $this->getUuid()->toString(),
            'email' => $this->getEmail(),
            'status' => $this->getStatus(),
            'avatar' => is_null($this->getAvatar()) ? null : $this->getAvatar()->getArrayCopy(),
            'detail' => is_null($this->getDetail()) ? null : $this->getDetail()->getArrayCopy(),
            'roles' => array_map(function (UserRoleEntity $role) {
                return $role->getArrayCopy();
            }, $this->getRoles()->getIterator()->getArrayCopy()),
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated()
        ];
    }

    /**
     * @return array
     */
    public function getStatuses()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_ACTIVE
        ];
    }
}
