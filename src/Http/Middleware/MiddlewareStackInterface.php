<?php

namespace Flashy\Http\Middleware;

use Flashy\ContainerResolver;
use Flashy\Http\Route\Router;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface MiddlewareStackInterface
{
    public function getMiddlewares() : array;

    public function pushMiddleware($middleware): MiddlewareStackInterface;

    public function popMiddleware();

    public function loadMiddlewares(): void;

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next): ResponseInterface;
}
