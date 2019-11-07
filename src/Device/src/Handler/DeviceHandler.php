<?php

declare(strict_types=1);

namespace Api\Device\Handler;

use Api\App\RestDispatchTrait;
use Api\Device\Form\InputFilter\UserAgentInputFilter;
use Api\Device\Service\DeviceService;
use Dot\AnnotatedServices\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;

/**
 * Class DeviceHandler
 * @package Api\Device\Handler
 */
class DeviceHandler implements RequestHandlerInterface
{
    use RestDispatchTrait;

    /**
     * @var DeviceService $deviceService
     */
    protected $deviceService;

    /**
     * DeviceHandler constructor.
     * @param HalResponseFactory $halResponseFactory
     * @param ResourceGenerator $resourceGenerator
     * @param DeviceService $deviceService
     *
     * @Inject({HalResponseFactory::class, ResourceGenerator::class, DeviceService::class})
     */
    public function __construct(
        HalResponseFactory $halResponseFactory,
        ResourceGenerator $resourceGenerator,
        DeviceService $deviceService
    ) {
        $this->responseFactory = $halResponseFactory;
        $this->resourceGenerator = $resourceGenerator;
        $this->deviceService = $deviceService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function put(ServerRequestInterface $request) : ResponseInterface
    {
        $inputFilter = (new UserAgentInputFilter())->getInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        return $this->responseFactory->createResponse($request,
            $this->resourceGenerator->fromObject(
                $this->deviceService->identify(
                    $inputFilter->getValue('userAgent')
                ),
                $request
            )
        );
    }
}
