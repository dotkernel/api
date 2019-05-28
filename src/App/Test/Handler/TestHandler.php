<?php

declare(strict_types=1);

namespace App\Test\Handler;

use Exception;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class TestHandler
 * @package App\Test\Handler
 */
class TestHandler implements RequestHandlerInterface
{
    /** @var EntityManager $entityManager */
    protected $entityManager;

    /**
     * TestHandler constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        return new JsonResponse([
            'message' => 'Welcome to DotKernel API!',
            'debug' => [
                'database' => $this->getDatabaseStatus(),
                'php' => PHP_VERSION
            ]
        ]);
    }

    /**
     * @return string
     */
    private function getDatabaseStatus()
    {
        try {
            $this->entityManager->getConnection()->connect();
            if ($this->entityManager->getConnection()->isConnected()) {
                return 'connected';
            }
        } catch (Exception $exception) {}

        return 'not connected';
    }
}
