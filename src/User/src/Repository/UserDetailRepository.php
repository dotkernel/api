<?php

declare(strict_types=1);

namespace Api\User\Repository;

use Doctrine\ORM\EntityRepository;
use Dot\AnnotatedServices\Annotation\Entity;

/**
 * @Entity(name="Api\User\Entity\UserDetail")
 * @extends EntityRepository<object>
 */
class UserDetailRepository extends EntityRepository
{
}
