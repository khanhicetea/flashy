<?php

namespace Flashy\Http;

use Flashy\Http\Route\Router;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface MiddlewareStackInterface
{
    public function __construct(ContainerInterface $container, Router $routing);

    public function getContainer(): ContainerInterface;

    public function getRouting(): Router;

    public function addMiddleware(callable $middleware): MiddlewareStackInterface;

    public function loadMiddlewares(): void;

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface;
}
