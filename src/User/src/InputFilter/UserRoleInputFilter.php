<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\App\InputFilter\Input\UuidInput;
use Core\App\InputFilter\AbstractInputFilter;

/**
 * @phpstan-type UserRoleDataType array{
 *     id: non-empty-string,
 * }
 * @extends AbstractInputFilter<UserRoleDataType>
 */
class UserRoleInputFilter extends AbstractInputFilter
{
    public function __construct()
    {
        $this->add(new UuidInput('id'));
    }
}
