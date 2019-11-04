<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Common\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * Class UserDetailEntity
 * @ORM\Entity()
 * @ORM\Table(name="user_detail")
 * @ORM\HasLifecycleCallbacks()
 * @package Api\User\Entity
 */
class UserDetailEntity extends AbstractEntity implements ArraySerializableInterface
{
    /**
     * @ORM\OneToOne(targetEntity="UserEntity", inversedBy="detail")
     * @ORM\JoinColumn(name="userUuid", referencedColumnName="uuid", nullable=false)
     * @var UserEntity $user
     */
    protected $user;

    /**
     * @ORM\Column(name="firstname", type="string", length=191, nullable=true)
     * @var $firstname
     */
    protected $firstname;

    /**
     * @ORM\Column(name="lastname", type="string", length=191, nullable=true)
     * @var $lastname
     */
    protected $lastname;

    /**
     * UserDetailEntity constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return UserEntity
     */
    public function getUser(): UserEntity
    {
        return $this->user;
    }

    /**
     * @param UserEntity $user
     * @return $this
     */
    public function setUser(UserEntity $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param $firstname
     * @return $this
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param $lastname
     * @return $this
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Return an array representation of the object
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return [
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated()
        ];
    }
}
