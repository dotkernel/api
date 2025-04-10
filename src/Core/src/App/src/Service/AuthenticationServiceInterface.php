<?php

declare(strict_types=1);

namespace Core\App\Service;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Storage\StorageInterface;

/**
 * The purpose of this interface is to be combined with \Laminas\Authentication\AuthenticationServiceInterface,
 * so that QA tools will not complain about the methods that are missing from the interface but are present in the
 * implementation: \Laminas\Authentication\AuthenticationService.
 */
interface AuthenticationServiceInterface
{
    public function getAdapter(): AdapterInterface;

    public function getStorage(): StorageInterface;
}
