<?php

declare(strict_types=1);

namespace Api\User\Repository;

use Doctrine\ORM\EntityRepository;
use Dot\AnnotatedServices\Annotation\Entity;

/**
 * Class UserDetailRepository
 * @package Api\User\Repository
 *
 * @Entity(name="Api\User\Entity\UserDetail")
 */
class UserDetailRepository extends EntityRepository
{

}
