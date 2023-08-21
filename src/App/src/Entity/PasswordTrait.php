<?php

declare(strict_types=1);

namespace Api\App\Entity;

use function password_hash;
use function password_verify;

use const PASSWORD_DEFAULT;

trait PasswordTrait
{
    public function usePassword(string $password): self
    {
        $this->password = $this->hashPassword($password);

        return $this;
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
