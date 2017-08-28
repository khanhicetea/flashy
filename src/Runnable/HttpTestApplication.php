<?php

namespace Flashy\Runnable;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Flashy\Http\Kernel;
use Psr\Container\ContainerInterface;
use function DI\get;

class HttpTestApplication
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function run(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->container->call([Kernel::class, 'run'], [
            $request,
            get('http.response')
        ]);

        return $response;
    }
}