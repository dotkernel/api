<?php

declare(strict_types=1);

namespace ApiTest\Unit\App\Exception;

use Api\App\Exception\RuntimeException;
use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;

class RuntimeExceptionTest extends TestCase
{
    public function testCreateReturnsInternalServerError(): void
    {
        $exception = RuntimeException::create('detail');

        $this->assertSame(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, $exception->getStatus());
    }
}
