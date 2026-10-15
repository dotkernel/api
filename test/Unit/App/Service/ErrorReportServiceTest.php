<?php

declare(strict_types=1);

namespace ApiTest\Unit\App\Service;

use Api\App\Exception\ForbiddenException;
use Api\App\Service\ErrorReportService;
use Api\App\Service\ErrorReportServiceInterface;
use Core\App\Message;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;

class ErrorReportServiceTest extends TestCase
{
    public function testDisabledErrorReportingThrowsForbidden(): void
    {
        $service = new ErrorReportService([
            ErrorReportServiceInterface::class => ['enabled' => false],
        ]);

        $this->expectExceptionObject(ForbiddenException::create(Message::ERROR_REPORT_NOT_ENABLED));

        $service->checkRequest(new ServerRequest());
    }
}
